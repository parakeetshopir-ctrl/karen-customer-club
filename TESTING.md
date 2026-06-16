# Testing Guide

## Prerequisites

- Local WordPress installation (5.0+)
- WooCommerce 4.0+ activated
- Test SMS gateway account (Melipayamak, Kavenegar, or Faraz)

## Installation Steps

1. **Upload Plugin**
   - Copy `karen-customer-club/` to `wp-content/plugins/`
   - Go to WordPress Dashboard → Plugins
   - Activate "باشگاه مشتریان کارن"

2. **Check Database Tables**
   - Open phpMyAdmin or database client
   - Verify tables created:
     - `wp_karen_sms_logs`
     - `wp_karen_sent_customers`

3. **Configure Settings**
   - Go to "باشگاه مشتریان کارن" → تنظیمات عمومی
   - Set check interval: 15 minutes
   - Select order statuses: Failed, Cancelled, Pending
   - Set scan period: 14 days
   - Set grace period: 14 days
   - Save

4. **Configure SMS Gateway**
   - Go to "باشگاه مشتریان کارن" → تنظیمات پیامک
   - Select SMS gateway
   - Enter API credentials
   - Enter sender number
   - Write message template: `سلام {first_name}، کوپن شما: {coupon}`
   - Save

5. **Configure Coupons**
   - Go to "باشگاه مشتریان کارن" → تنظیمات پیامک
   - Set coupon prefix: KAREN
   - Select discount type: Percent
   - Set discount amount: 10%
   - Set expiration: 24 hours
   - Save

## Testing Scenarios

### Scenario 1: Test SMS Sending

1. Go to "باشگاه مشتریان کارن" → تست ارسال پیامک
2. Enter test phone: 09123456789
3. Message: سلام! این پیامک تست است
4. Click "ارسال تست"
5. Verify message arrives

### Scenario 2: Create Failed Order

1. Create a new order with:
   - Customer phone: 09123456789
   - Status: Failed
2. Wait 15 minutes (or change cron interval to 1 minute for testing)
3. Go to "باشگاه مشتریان کارن" → گزارش‌ها
4. Verify SMS log entry exists
5. Check if customer received SMS with coupon

### Scenario 3: Prevent Duplicate SMS

1. Create another failed order with same phone
2. Wait 15 minutes
3. Verify SMS log shows only one entry for this phone
4. Verify no duplicate SMS sent

### Scenario 4: Grace Period Test

1. Create failed order with phone: 09112345678
2. Wait and verify SMS sent
3. Create completed order with same phone (recent)
4. Create another failed order with same phone
5. Verify no SMS sent for new failed order

### Scenario 5: Coupon Usage

1. Get coupon code from SMS log
2. Create new order with this coupon
3. Verify discount applied
4. Go to WooCommerce → Coupons
5. Verify coupon shows as used (1/1)

## Debugging

### Check Cron Execution

```php
// In wp-cli or debug terminal
wp cron test
```

### View SMS Logs

```php
global $wpdb;
$logs = $wpdb->get_results("SELECT * FROM wp_karen_sms_logs ORDER BY created_at DESC LIMIT 10");
echo '<pre>';
print_r($logs);
echo '</pre>';
```

### Check Sent Customers

```php
global $wpdb;
$customers = $wpdb->get_results("SELECT * FROM wp_karen_sent_customers");
echo '<pre>';
print_r($customers);
echo '</pre>';
```

## Common Issues

### SMS Not Sending

- Check SMS gateway credentials
- Verify sender number is correct
- Check phone normalization: 09123456789 → 989123456789
- Check SMS log for error message

### Cron Not Running

- Check WordPress cron configuration
- Run: `wp cron event list`
- Verify `karen_process_orders_cron` is scheduled

### Coupon Not Applied

- Check coupon code format (max 15 chars)
- Check coupon expiration date
- Check usage limit (should be 1)
- Verify phone matching in logs

### Database Error

- Check table structure:
  ```sql
  SHOW CREATE TABLE wp_karen_sms_logs;
  SHOW CREATE TABLE wp_karen_sent_customers;
  ```
- Verify PHP version: 7.4+
- Check WooCommerce version: 4.0+

## Performance Testing

- Create 1000+ test orders
- Monitor CPU and memory usage
- Check query execution time
- Verify batch processing works correctly
- Check for database locks

## Security Testing

- Test SQL injection attempts
- Test XSS vulnerabilities in admin
- Verify nonce checks
- Test capability checks
- Test input sanitization

## Browser Testing

- Chrome/Chromium
- Firefox
- Safari
- Edge
- Mobile browsers (iOS Safari, Chrome Android)

## Final Checklist

- [ ] Installation works without errors
- [ ] Tables created successfully
- [ ] Admin pages load correctly
- [ ] Settings can be saved
- [ ] SMS test works
- [ ] Cron processes orders
- [ ] Coupons created successfully
- [ ] No duplicate SMS sent
- [ ] Grace period works
- [ ] Reports display correctly
- [ ] No PHP errors in logs
- [ ] No JavaScript errors in console
