<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class AdminCorrectRequest extends CorrectionRequest
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
        $rules = parent::rules();

        $rules['reason'] = 'required|string'; 
        return $rules;
    }

    public function messages()
    {
        $messages = parent::messages();

        // 管理者用のエラーメッセージを追加または変更
        $messages['reason.required'] = '備考、修正した理由を入力してください。';
        return $messages;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 入力データを配列に強制変換
            $this->break_start_time = (array) $this->break_start_time;
            $this->break_end_time = (array) $this->break_end_time;
    
            // 入力値の前処理（全角から半角に変換）
            $this->start_time = mb_convert_kana($this->start_time, 'a');
            $this->end_time = mb_convert_kana($this->end_time, 'a');

             // 全角チェック
            if (preg_match('/[^\x20-\x7E]/u', $this->start_time)) {
              $validator->errors()->add('start_time', '出勤時間は半角で入力してください');
              return; // エラーがあれば後続処理を停止
            }
            if (preg_match('/[^\x20-\x7E]/u', $this->end_time)) {
              $validator->errors()->add('end_time', '退勤時間は半角で入力してください');
              return; // エラーがあれば後続処理を停止
            }

            foreach ($this->break_start_time as $index => $breakStartTime) {
              $breakStartTime = mb_convert_kana($breakStartTime, 'a');
              $breakEndTime = mb_convert_kana($this->break_end_time[$index] ?? '', 'a');

              if (preg_match('/[^\x20-\x7E]/u', $breakStartTime)) {
                 $validator->errors()->add("break_start_time.$index", '休憩開始時間は半角で入力してください');
                 return; // エラーがあれば後続処理を停止
              }
              if (preg_match('/[^\x20-\x7E]/u', $breakEndTime)) {
                 $validator->errors()->add("break_end_time.$index", '休憩終了時間は半角で入力してください');
                 return; // エラーがあれば後続処理を停止
              }
            }
    
            // 出勤・退勤時間のバリデーション
            if ($this->start_time && $this->end_time) {
                try{
                $startTime = Carbon::createFromFormat('H:i', $this->start_time);
                $endTime = Carbon::createFromFormat('H:i', $this->end_time);
    
                if ($startTime->gte($endTime)) {
                    $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切な値です');
                }
              } catch (\Exception $e) {
                $validator->errors()->add('start_time', '出勤時間の形式が不正です (例: 09:00)');
                $validator->errors()->add('end_time', '退勤時間の形式が不正です (例: 18:00)');
              }
            }
    
            // 休憩時間のバリデーション
            if ($this->start_time && $this->end_time && $this->break_start_time && $this->break_end_time) {
                try{
                $startTime = Carbon::createFromFormat('H:i', $this->start_time);
                $endTime = Carbon::createFromFormat('H:i', $this->end_time);
    
                foreach ($this->break_start_time as $index => $breakStartTime) {
                    $breakStart = Carbon::createFromFormat('H:i', $breakStartTime);
                    $breakEnd = Carbon::createFromFormat('H:i',  $this->break_end_time[$index] ?? '');
    
                    if (!$breakStart || !$breakEnd || $breakStart->lt($startTime) || $breakEnd->gt($endTime) || $breakStart->gte($breakEnd)) {
                        $validator->errors()->add('break_start_time', '休憩時間が勤務時間外です');
                        break;
                    }
                }
            } catch (\Exception $e) {
                $validator->errors()->add('break_start_time', '休憩時間の形式が不正です。半角数字で入力してください (例: 12:00)');
                $validator->errors()->add('break_end_time', '休憩時間の形式が不正です。半角数字で入力してください (例: 13:00)');
            }   
          }
        });
    }
}
