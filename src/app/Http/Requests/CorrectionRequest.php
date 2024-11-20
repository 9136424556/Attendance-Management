<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class CorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'year' => 'required|digits:4', // 年は4桁
            'date' => 'required|regex:/^\d{2}-\d{2}$/', // 月日はm-d形式
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'break_start_time' => 'nullable|array',
            'break_end_time' => 'nullable|array',
            'reason' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'date.regex' => '日付はMM-DDの形式で入力してください。',
            'reason.required' => '修正を申請する理由を入力してください',
            'start_time.date_format' => '出勤時間の形式が不適切です。半角の〇〇:〇〇形式で時間を入力してください',
            'end_time.date_format' => '退勤時間の形式が不適切です。半角の〇〇:〇〇形式で時間を入力してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 入力値の前処理（全角から半角に変換）
            $this->start_time = mb_convert_kana($this->start_time, 'a');
            $this->end_time = mb_convert_kana($this->end_time, 'a');
            // 出勤・退勤時間のバリデーション
            if ($this->start_time && $this->end_time) {
                $startTime = Carbon::createFromFormat('H:i', $this->start_time);
                $endTime = Carbon::createFromFormat('H:i', $this->end_time);

                if ($startTime->gte($endTime)) {
                    $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切な値です');
                }
            }

            // 休憩時間のバリデーション
            if ($this->start_time && $this->end_time && !empty($this->break_start_time) && !empty($this->break_end_time)) {
                $startTime = Carbon::createFromFormat('H:i', $this->start_time);
                $endTime = Carbon::createFromFormat('H:i', $this->end_time);

                foreach ($this->break_start_time as $index => $breakStartTime) {
                    // 全角文字を半角に変換
                    $breakStartTime = mb_convert_kana($breakStartTime, 'a');
                    $breakEndTime = mb_convert_kana($this->break_end_time[$index] ?? '', 'a');

                     if (!$breakStartTime || !$breakEndTime) {
                         $validator->errors()->add('break_start_time', '休憩時間が不完全です');
                         continue; // 次のループに進む
                     }

                    // 時刻をCarbonでパース
                    $breakStart = Carbon::createFromFormat('H:i', $breakStartTime);
                    $breakEnd = Carbon::createFromFormat('H:i',$breakEndTime);
                    

                    // 勤務時間外にある場合 - break_start_time のエラー
                    if ($breakStart->lt($startTime) || $breakStart->gte($endTime)) {
                        $validator->errors()->add("break_start_time.$index", '休憩時間が勤務時間外です');
                    }
                    
                    // 勤務時間外にある場合 - break_end_time のエラー
                    if ($breakEnd->lt($startTime) || $breakEnd->gt($endTime)) {
                        $validator->errors()->add("break_end_time.$index", '休憩時間が勤務時間外です');
                    }
                    if ($breakStart->gte($breakEnd)) {
                        $validator->errors()->add("break_start_time.$index", '休憩時間が勤務時間外です');
                    }
                }
            }
             // year と date を結合して work_date を作成
            $work_date = $this->year . '-' . $this->date;

            // work_date の形式チェック（例: Y-m-d）
            if (!Carbon::hasFormat($work_date, 'Y-m-d')) {
              $validator->errors()->add('work_date', '日付が正しい形式ではありません');
            }
        });
    }
}
