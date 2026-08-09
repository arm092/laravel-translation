<?php

namespace Arm092\Translation\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Arm092\Translation\Actions\WriteTranslation;
use Arm092\Translation\Authorization\TranslationManagerAuthorizer;
use Arm092\Translation\Drivers\Translation;
use Arm092\Translation\Support\TranslationInputRules;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TranslationInput extends Component
{
    #[Locked]
    public string $language;

    #[Locked]
    public string $group;

    #[Locked]
    public string $translationKey;

    public string $value = '';

    #[Locked]
    public string $persistedValue = '';

    public string $status = 'idle';

    public function mount(
        ?string $initialTranslation,
        string $language,
        string $group,
        string $translationKey,
    ): void {
        app(TranslationManagerAuthorizer::class)->authorize();

        $this->language = $language;
        $this->group = $group;
        $this->translationKey = $translationKey;
        $this->value = $initialTranslation ?? '';
        $this->persistedValue = $this->value;

        Validator::make($this->payload(), TranslationInputRules::get())->validate();
    }

    public function save(): void
    {
        app(TranslationManagerAuthorizer::class)->authorize();

        if ($this->value === $this->persistedValue) {
            $this->status = 'idle';

            return;
        }

        try {
            Validator::make($this->payload(), TranslationInputRules::get())->validate();

            app(WriteTranslation::class)->handle(
                app(Translation::class),
                $this->language,
                null,
                $this->group,
                $this->translationKey,
                $this->value,
                ! Str::contains($this->group, 'single'),
            );

            $this->persistedValue = $this->value;
            $this->status = 'saved';
            $this->resetErrorBag();
        } catch (ValidationException $exception) {
            $this->status = 'error';
            $this->setErrorBag($exception->validator->errors());
        } catch (\Throwable $exception) {
            report($exception);
            $this->status = 'error';
        }
    }

    public function render(): View
    {
        return view('translation::livewire.translation-input');
    }

    /**
     * @return array<string, string|null>
     */
    private function payload(): array
    {
        return [
            'language' => $this->language,
            'namespace' => null,
            'group' => $this->group,
            'key' => $this->translationKey,
            'value' => $this->value,
        ];
    }
}
