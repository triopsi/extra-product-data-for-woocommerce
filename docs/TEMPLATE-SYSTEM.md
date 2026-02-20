# Template System Documentation

## Overview

The Extra Product Data for WooCommerce plugin uses a lightweight template system similar to Twig/Jinja2 for field rendering.

## Development

This plugin uses Composer autoloading for its PHP classes. Run `composer install` to generate the autoloader. The autoload configuration includes a classmap for the WordPress-style `class-*.php` files in `src/classes/`.

## Template Engine Class

The `Exprdawc_Template_Engine` class provides:

- **Variable Interpolation**: `{{ variable }}` for escaped output
- **Raw Output**: `{{{ variable }}}` for unescaped output
- **Conditionals**: `{% if condition %}...{% endif %}`
- **Loops**: `{% foreach array as item %}...{% endforeach %}`
- **Includes**: `{% include 'template.php' %}`
- **Auto-Escaping**: Automatic context-aware escaping
- **Template Caching**: Performance optimization

## Template Helpers

The `Exprdawc_Template_Helpers` class (Alias: `H`) provides useful functions:

### Escaping Functions

```php
H::e( $text )           // HTML escape
H::attr( $text )        // Attribute escape
H::js( $text )          // JavaScript escape
H::url( $url )          // URL escape
H::textarea( $text )    // Textarea escape
```

### HTML Helpers

```php
H::classes( $classes )              // CSS classes string
H::attrs( $attributes )             // HTML attributes string
H::data_attrs( $data )              // Data attributes string
H::join( $array, $glue )            // Join array
```

### Form Helpers

```php
H::checked( $value, $option )       // Checked attribute
H::selected( $value, $option )      // Selected attribute
H::id( $string )                    // Sanitize ID
```

### Utility Functions

```php
H::is_empty( $value )               // Empty check
H::is_set( $value )                 // Isset check
H::get( $array, $key, $default )    // Array value with default
H::price( $price )                  // Format price
```

## Template Syntax

### Variables

```html
<!-- Escaped output (safe) -->
<div>{{ field.label }}</div>

<!-- Raw output (unescaped) -->
<div>{{{ field.raw_html }}}</div>
```

### Conditionals

```html
{% if field.required %}
    <span class="required">*</span>
{% endif %}

{% if !field.description %}
    <!-- No description available -->
{% endif %}
```

### Loops

```html
{% foreach field.options as option %}
    <option value="{{ option.value }}">
        {{ option.label }}
    </option>
{% endforeach %}
```

### Includes

```html
{% include 'partials/field-wrapper-start.php' %}
<input type="text" />
{% include 'partials/field-wrapper-end.php' %}
```

## Template Examples

### Simple Text Field

```php
<?php
use Triopsi\Exprdawc\Exprdawc_Template_Helpers as H;

$field = $field_args ?? array();
?>

<label for="<?php echo H::attr( $field['id'] ); ?>">
    <?php echo H::e( $field['label'] ); ?>
</label>

<input type="text"
    id="<?php echo H::attr( $field['id'] ); ?>"
    name="<?php echo H::attr( $field['name'] ); ?>"
    value="<?php echo H::attr( $field['value'] ?? '' ); ?>"
    class="<?php echo H::classes( $field['input_class'] ); ?>"
/>
```

### Checkbox with Options

```php
<?php foreach ( $field['options'] as $option ) : ?>
    <?php
    $option_id = H::id( $field['id'] . '-' . $option['value'] );
    $checked   = H::checked( $field['value'], $option['value'] );
    ?>
    
    <div class="checkbox-option">
        <input type="checkbox"
            id="<?php echo H::attr( $option_id ); ?>"
            name="<?php echo H::attr( $field['name'] ); ?>[]"
            value="<?php echo H::attr( $option['value'] ); ?>"
            <?php echo $checked; ?>
        />
        <label for="<?php echo H::attr( $option_id ); ?>">
            <?php echo H::e( $option['label'] ); ?>
        </label>
    </div>
<?php endforeach; ?>
```

### Select with Data Attributes

```php
<select name="<?php echo H::attr( $field['name'] ); ?>">
    <?php foreach ( $field['options'] as $option ) : ?>
        <?php
        $data_attrs = array(
            'price-adjustment' => $option['price_adjustment_value'] ?? '',
            'price-type'       => $option['price_adjustment_type'] ?? 'fixed',
        );
        ?>
        
        <option value="<?php echo H::attr( $option['value'] ); ?>"
            <?php echo H::selected( $field['value'], $option['value'] ); ?>
            <?php echo H::data_attrs( $data_attrs ); ?>
        >
            <?php echo H::e( $option['label'] ); ?>
        </option>
    <?php endforeach; ?>
</select>
```

## Using the Template Engine

### Basic Rendering

```php
$engine = new Exprdawc_Template_Engine( EXPRDAWC_FIELDS_TEMPLATES_PATH );

$engine->render( 'checkbox.php', array(
    'field_args'         => $field,
    'required_string'    => '<span class="required">*</span>',
    'custom_attributes'  => array( 'required', 'data-conditional="true"' ),
) );
```

### With Caching

```php
// Caching enabled (default)
$engine = new Exprdawc_Template_Engine( $path, true );

// Caching disabled
$engine = new Exprdawc_Template_Engine( $path, false );

// Clear cache
Exprdawc_Template_Engine::clear_cache();
```

### Field Rendering

```php
$engine = new Exprdawc_Template_Engine( EXPRDAWC_FIELDS_TEMPLATES_PATH );

// Render with echo
$engine->render( 'text.php', $variables, true );

// Render and return string
$html = $engine->render( 'text.php', $variables, false );
```

## Best Practices

### 1. Always Use Escaping

```php
// Good
<?php echo H::e( $field['label'] ); ?>

// Bad
<?php echo $field['label']; ?>
```

### 2. Null-Safe Access

```php
// Good
<?php echo H::attr( $field['value'] ?? '' ); ?>

// Bad
<?php echo H::attr( $field['value'] ); ?>
```

### 3. Use Helper Functions

```php
// Good
<?php echo H::classes( $field['input_class'] ); ?>

// Bad
<?php echo esc_attr( implode( ' ', $field['input_class'] ) ); ?>
```

### 4. Group Data Attributes

```php
// Good
$data_attrs = array(
    'price-adjustment' => $value,
    'price-type'       => $type,
);
echo H::data_attrs( $data_attrs );

// Bad
echo 'data-price-adjustment="' . esc_attr( $value ) . '" ';
echo 'data-price-type="' . esc_attr( $type ) . '"';
```

## Migrating from Old Templates

### Before

```php
echo '<label class="' . esc_attr( implode( ' ', $field_args['label_class'] ) ) . '">';
echo esc_html( $field_args['label'] ) . $required_string;
echo '</label>';
```

### After

```php
<label class="<?php echo H::classes( $field['label_class'] ); ?>">
    <?php echo H::e( $field['label'] ); ?>
    <?php echo $required_string; // Already escaped ?>
</label>
```

## Performance

- Template caching is enabled by default
- Cache keys are based on template path + variables
- Cache is cleared on each request (no persistent caching)
- For development: disable caching

## Extensibility

### Custom Template Functions

```php
class My_Custom_Helpers extends Exprdawc_Template_Helpers {
    public static function my_custom_function( $value ) {
        // Custom logic
        return $value;
    }
}
```

### Custom Templates

1. Create template in `/src/templates/view/fields/my-field.php`
2. Use helper functions
3. Render template via engine

## Debugging

```php
// Enable WP_DEBUG for template warningsedel
define( 'WP_DEBUG', true );

// Template not found -> trigger_error()
// Invalid variables -> ''
```