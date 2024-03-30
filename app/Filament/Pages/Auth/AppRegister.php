<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register;
use Override;

class AppRegister extends Register
{
    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->required()
                    ->email()
                    ->maxLength(255),
            ]);
    }

    #[Override]
    public function register(): ?RegistrationResponse
    {
        $this->rateLimit(2);

        $this->data = $this->form->getState();

        $user = User::create($this->data);

        Notification::make()
            ->title('Cadastro Realizado!')
            ->body("Enviamos um email para {$user->email} com seus dados de acesso.")
            ->success()
            ->send();

        $this->reset();

        return null;
    }
}
