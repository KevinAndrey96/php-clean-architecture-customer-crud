<?php

declare(strict_types=1);

namespace CustomerCrud\Http;

use RuntimeException;

/**
 * Plain PHP templates. Templates print through e(), so user data is always HTML-escaped.
 */
final class View
{
    private const LAYOUT = 'layout';

    public function __construct(
        private readonly string $templateDir,
        private readonly Csrf $csrf,
        private readonly Flash $flash,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, string $title, array $data = []): string
    {
        $content = $this->capture($template, $data);

        return $this->capture(self::LAYOUT, [
            'title' => $title,
            'content' => $content,
            'flash' => $this->flash->pull(),
        ]);
    }

    public function e(string|int|null $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function csrfField(): string
    {
        return sprintf('<input type="hidden" name="%s" value="%s">', Csrf::FIELD, $this->e($this->csrf->token()));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function capture(string $template, array $data): string
    {
        $file = $this->templateDir.'/'.$template.'.php';
        if (!is_file($file)) {
            throw new RuntimeException(sprintf('view: template %s not found', $template));
        }

        $data['view'] = $this;

        ob_start();
        try {
            (static function (string $__file, array $__data): void {
                extract($__data, EXTR_SKIP);
                require $__file;
            })($file, $data);
        } catch (\Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return (string) ob_get_clean();
    }
}
