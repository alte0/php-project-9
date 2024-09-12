<?php

namespace App;

use Valitron\Validator;

final class UrlValidator
{
    private string $errorText = '';
    private Validator $v;

    public function __construct(array $formData, string $fieldName = 'url.name')
    {
        $this->v = new Validator($formData);
        $this->v->rule('required', $fieldName)->message('URL не должен быть пустым');
        $this->v->rule('url', $fieldName)->message('Некорректный URL');
        $this->v->rule('lengthMax', $fieldName, 255)->message('Длина URL максимум 255 символов');

        if (!$this->v->validate()) {
            $this->setErrorText($fieldName);
        }
    }

    /** Установка текста ошибки валидации
     * @param string $fieldName
     * @return void
     */
    private function setErrorText(string $fieldName): void
    {
        $error = $this->v->errors();

        if (\is_array($error) && \array_key_exists($fieldName, $error)) {
            $this->errorText = $error[$fieldName][0];
        }
    }

    /** Проверка на наличие ошибки валидации
     * @return bool
     */
    public function isHaveError(): bool
    {
        return $this->errorText !== '';
    }

    /** Получение текста ошибки валидации
     * @return string
     */
    public function getErrorText(): string
    {
        return $this->errorText;
    }
}
