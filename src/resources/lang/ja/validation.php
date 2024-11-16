<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attributeを承認してください。',
    'active_url' => ':attributeは、有効なURLではありません。',
    'after' => ':attributeには、:date以降の日付を指定してください。',
    'alpha' => ':attributeはアルファベットのみがご利用できます。',
    'alpha_dash' => ':attributeは英数字とダッシュ(-)及び下線(_)がご利用できます。',
    'alpha_num' => ':attributeは英数字がご利用できます。',
    'array' => ':attributeは配列でなくてはなりません。',
    'before' => ':attributeには、:date以前の日付を指定してください。',
    'between' => [
        'numeric' => ':attributeは、:minから、:maxまでの数字を指定してください。',
        'file' => ':attributeは、:min kBから:max kBまでのファイルを指定してください。',
        'string' => ':attributeは、:min文字から:max文字までの文字を指定してください。',
        'array' => ':attributeは、:min個から:max個までのアイテムを指定してください。',
    ],
    'boolean' => ':attributeは、trueかfalseを指定してください。',
    'confirmed' => ':attributeと、確認フィールドとが、一致していません。',
    'date' => ':attributeは有効な日付ではありません。',
    'date_format' => ':attributeは:format形式で入力してください。',
    'different' => ':attributeと:otherには、異なるものを指定してください。',
    'digits' => ':attributeは:digits桁で入力してください。',
    'digits_between' => ':attributeは:min桁から:max桁の間で入力してください。',
    'dimensions' => ':attributeの画像サイズが無効です。',
    'distinct' => ':attributeには異なる値を指定してください。',
    'email' => ':attributeは、有効なメールアドレス形式で入力してください。',
    'exists' => '選択された:attributeは正しくありません。',
    'file' => ':attributeにはファイルを指定してください。',
    'filled' => ':attributeは必須です。',
    'image' => ':attributeには画像ファイルを指定してください。',
    'in' => '選択された:attributeは正しくありません。',
    'in_array' => ':attributeは:otherに含まれていません。',
    'integer' => ':attributeは整数で指定してください。',
    'ip' => ':attributeは、有効なIPアドレスを指定してください。',
    'ipv4' => ':attributeは、有効なIPv4アドレスを指定してください。',
    'ipv6' => ':attributeは、有効なIPv6アドレスを指定してください。',
    'json' => ':attributeは、有効なJSON文字列を指定してください。',
    'max' => [
        'numeric' => ':attributeは:max以下で入力してください。',
        'file' => ':attributeは、:max kB以下のファイルを指定してください。',
        'string' => ':attributeは、:max文字以下で入力してください。',
        'array' => ':attributeは:max個以下指定してください。',
    ],
    'mimes' => ':attributeには:valuesタイプのファイルを指定してください。',
    'mimetypes' => ':attributeには:valuesタイプのファイルを指定してください。',
    'min' => [
        'numeric' => ':attributeは、:min以上で指定してください。',
        'file' => ':attributeは、:min kB以上のファイルを指定してください。',
        'string' => ':attributeは、:min文字以上で指定してください。',
        'array' => ':attributeは:min個以上指定してください。',
    ],
    'not_in' => '選択された:attributeは正しくありません。',
    'not_regex' => ':attributeの形式が正しくありません。',
    'numeric' => ':attributeは数字で入力してください。',
    'present' => ':attributeが存在していません。',
    'regex' => ':attributeに正しい形式を指定してください。',
    'required' => ':attributeは必須です。',
    'required_if' => ':otherが:valueの場合、:attributeを指定してください。',
    'required_unless' => ':otherが:valueでない場合、:attributeは必須です。',
    'required_with' => ':valuesを指定する場合は、:attributeも指定してください。',
    'required_with_all' => ':valuesを指定する場合は、:attributeも指定してください。',
    'required_without' => ':valuesを指定しない場合は、:attributeを指定してください。',
    'required_without_all' => ':valuesのどれも指定しない場合は、:attributeを指定してください。',
    'same' => ':attributeと:otherが一致しません。',
    'size' => [
        'numeric' => ':attributeは:sizeを指定してください。',
        'file' => ':attributeは、:size kBのファイルを指定してください。',
        'string' => ':attributeは:size文字で指定してください。',
        'array' => ':attributeは:size個指定してください。',
    ],
    'string' => ':attributeは文字を指定してください。',
    'timezone' => ':attributeは、有効なタイムゾーンを指定してください。',
    'unique' => ':attributeの値は既に存在しています。',
    'uploaded' => ':attributeのアップロードに失敗しました。',
    'url' => ':attributeに正しい形式を指定してください。',

    // 特定のバリデーションメッセージの上書き
    'mimes' => '画像は jpg または svg のファイル形式でなければなりません。',

    'attributes' => [
        'img' => '画像',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'email' => [
            'unique' => 'このメールアドレスは既に登録されています',
            'exists' => 'メールアドレスまたはパスワードが間違っています',
        ],
        'password' => [
            'exists' => 'パスワードが間違っています',
        ],
        
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],
];
