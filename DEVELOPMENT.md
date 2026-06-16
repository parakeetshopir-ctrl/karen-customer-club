# Development Guidelines

## Code Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Use proper escaping functions: `esc_html()`, `esc_attr()`, `esc_url()`, etc.
- Sanitize all inputs with `sanitize_*()` functions
- Use nonces for security
- Always check user capabilities with `current_user_can()`

## File Organization

- Keep classes in separate files
- Use namespace-like naming: `class-karen-*.php`
- Admin files in `admin/` directory
- Core functionality in `includes/` directory
- Assets (CSS/JS) in `assets/` directory

## Database

- Custom tables: `wp_karen_sms_logs`, `wp_karen_sent_customers`
- Use `dbDelta()` for creating/updating tables
- Always use prepared statements with `$wpdb->prepare()`

## Hooks & Filters

- Use WordPress hooks for extensibility
- Naming convention: `karen_*` prefix
- Document all hooks in code

## Version Management

- Increment version in:
  - `karen-customer-club.php` (Plugin header)
  - `composer.json`
- Update changelog in `README.md` and `readme.txt`

## Testing

- Test on WordPress 5.0+ and 6.0+
- Test with WooCommerce 4.0+
- Test with PHP 7.4+
- Test all SMS gateways
- Verify database operations

## Security

- Use `wp_nonce_field()` and `check_ajax_referer()` for AJAX
- Validate all user inputs
- Escape all output
- Use `manage_options` capability for admin pages
- Sanitize file operations

## Performance

- Use batch processing for large datasets
- Index database queries
- Cache frequently accessed data
- Minimize JavaScript and CSS
- Don't run heavy operations on every page load

## Future Enhancements

- Phase 2: Cashback system
- Phase 3: Loyalty program levels
- Phase 4: API integration
- Phase 5: Mobile app support
