# Иконки

Общие правила проекта — в [AGENTS.md](../../AGENTS.md).

- Все статичные иконки хранить в общем SVG-спрайте: `/assets/images/sprite.svg`.
- URL спрайта получать через `adem_svg_sprite_url( 'i-car' )`: хелпер сам добавляет ID символа.
	Не собирать URL вручную через `get_template_directory_uri()`.
- В коде вставлять иконку через `<svg>` + `<use>`, а не инлайнить SVG-разметку напрямую и не подключать иконку как
	`<img>`:

```php
<svg width="21" height="18">
	<use xlink:href="<?php echo esc_url( adem_svg_sprite_url( 'i-car' ) ); ?>"></use>
</svg>
```

- `width`/`height` у `<svg>` указывать под конкретный кейс использования (реальные размеры иконки в макете).
- `id` символа в спрайте — с префиксом `i-` (`i-burger`, `i-phone`, `i-arrow` и т.д.).

## Чего НЕ делать

- Не инлайнить SVG-разметку иконок и не подключать иконки через `<img>` — только через `<use>` и спрайт
	`/assets/images/sprite.svg`.
