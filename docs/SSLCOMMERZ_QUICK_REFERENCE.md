# SSLCOMMERZ Integration - Quick Reference

## Quick Setup Checklist

### 1. Environment (.env)
```env
SSLCZ_TESTMODE=true
SSLCZ_STORE_ID=your_store_id
SSLCZ_STORE_PASSWORD=your_store_password
IS_LOCALHOST=true
```

### 2. Admin Panel Settings
Settings > Payment Settings > SSLCOMMERZ
- Enter Store ID
- Enter Store Password
- Toggle Sandbox Mode

### 3. Routes (routes/web.php) - Lines 182-191
```php
// MUST be before catch-all routes
Route::any('/pay', [SslCommerzPaymentController::class, 'index']);
Route::post('/success', [SslCommerzPaymentController::class, 'success']);
Route::post('/fail', [SslCommerzPaymentController::class, 'fail']);
Route::post('/cancel', [SslCommerzPaymentController::class, 'cancel']);
Route::post('/ipn', [SslCommerzPaymentController::class, 'ipn']);
```

## Payment Flow Summary

```
1. User clicks "Confirm Order" with "Online Payment"
2. Order created with trx_id
3. Frontend redirects to: /pay?trx_id={trx_id}
4. Controller calls SSLCOMMERZ API
5. User redirected to SSLCOMMERZ gateway
6. After payment, callback to /success or /fail
7. IPN sent to /ipn (server-to-server)
8. Order completed
```

## Key File Locations

| File | Purpose | Key Lines |
|------|---------|-----------|
| `app/Http/Controllers/SslCommerzPaymentController.php` | Main Controller | 38-229 (index), 246-372 (success), 446-592 (ipn) |
| `app/Library/SslCommerz/SslCommerzNotification.php` | SSLCOMMERZ Library | 291-334 (setRequiredInfo) |
| `resources/js/components/frontend/pages/cart_new.vue` | Checkout Page | 1058-1087 (redirect) |
| `config/sslcommerz.php` | Configuration | All |
| `routes/web.php` | Routes Definition | 182-191 |

## Test Cards (Sandbox)

**Success**: 4111111111111111 (Visa)
**Fail**: 4000056655665556 (Visa)
**CVV**: Any 3 digits
**Expiry**: Any future date

## Common Commands

```bash
# Check routes
php artisan route:list | grep -E "(pay|success|fail|ipn)"

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# View logs
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log | grep SSLCOMMERZ
```

## Order Status Codes

- `0` = Pending
- `1` = Completed
- `-1` = Failed

## Currency Notes

- SSLCOMMERZ processes in **BDT only**
- Other currencies auto-converted
- BDT must exist in `currencies` table

## Debugging

Enable debug logs in controller:
```php
Log::info('SSLCOMMERZ Debug', ['data' => $data]);
```

## Important URLs

- Sandbox: https://sandbox.sslcommerz.com
- Production: https://securepay.sslcommerz.com
- Dashboard: https://merchant.sslcommerz.com
- Dev Docs: https://developer.sslcommerz.com/

## Support Issues & Solutions

| Issue | Solution |
|-------|----------|
| 500 Error | Clear caches, check PHP version >= 8.2 |
| 404 on /pay | Move SSLCOMMERZ routes before catch-all |
| Store Credential Error | Use API password, NOT panel password |
| Column is_default | Already fixed in code |
| Payment not completing | Check logs, verify credentials |

## Custom Data Passed to Callbacks

- `value_a` = Order code
- `value_b` = Reserved
- `value_c` = Reserved
- `value_d` = Reserved

Access in callbacks:
```php
$code = $request->input('value_a');
```

## Database Settings Check

```php
// Check if credentials are set
$store_id = settingHelper('sslcommerz_id');
$store_password = settingHelper('sslcommerz_password');
$is_sandbox = settingHelper('is_sslcommerz_sandbox_mode_activated') == 1;
```

---

*For detailed documentation, see: SSLCOMMERZ_INTEGRATION.md*