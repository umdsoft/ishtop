<?php

namespace App\Enums;

enum QuestionType: string
{
    case KNOCKOUT = 'knockout';
    case SINGLE_CHOICE = 'single_choice';
    case MULTI_SELECT = 'multi_select';
    case NUMBER_RANGE = 'number_range';
    case OPEN_TEXT = 'open_text';
    case FILE_UPLOAD = 'file_upload';

    public function label(): string
    {
        return match ($this) {
            self::KNOCKOUT => 'Knockout (Ha/Yo\'q)',
            self::SINGLE_CHOICE => 'Bitta tanlov',
            self::MULTI_SELECT => 'Ko\'p tanlov',
            self::NUMBER_RANGE => 'Raqam/Diapazon',
            self::OPEN_TEXT => 'Ochiq javob',
            self::FILE_UPLOAD => 'Fayl yuklash',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::KNOCKOUT => 'heroicon-o-shield-exclamation',
            self::SINGLE_CHOICE => 'heroicon-o-check-circle',
            self::MULTI_SELECT => 'heroicon-o-list-bullet',
            self::NUMBER_RANGE => 'heroicon-o-hashtag',
            self::OPEN_TEXT => 'heroicon-o-document-text',
            self::FILE_UPLOAD => 'heroicon-o-paper-clip',
        };
    }

    public function hasOptions(): bool
    {
        return in_array($this, [self::SINGLE_CHOICE, self::MULTI_SELECT]);
    }

    public function isAutoScored(): bool
    {
        return $this !== self::OPEN_TEXT;
    }
}
