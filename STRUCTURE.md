# Project Folder Structure
```
karen-customer-club/
├── karen-customer-club.php          # Main plugin file
├── uninstall.php                    # Uninstall handler
├── composer.json                    # Composer configuration
├── package.json                     # NPM configuration (if needed)
├── README.md                        # Full documentation in Persian
├── readme.txt                       # WordPress.org readme
├── .gitignore                       # Git ignore rules
│
├── admin/
│   ├── class-karen-admin.php        # Admin class
│   └── pages/
│       ├── dashboard.php            # Dashboard page
│       ├── settings.php             # General settings
│       ├── sms-settings.php         # SMS & Coupon settings
│       ├── reports.php              # Reports page
│       ├── coupons.php              # Coupons list
│       └── sms-test.php             # SMS test page
│
├── includes/
│   ├── class-karen-plugin.php       # Main plugin class
│   ├── class-karen-db.php           # Database operations
│   ├── class-karen-normalizer.php   # Phone normalization
│   ├── class-karen-coupon.php       # Coupon management
│   ├── class-karen-sms.php          # SMS gateway manager
│   ├── class-karen-settings.php     # Settings manager
│   ├── class-karen-cron.php         # Cron job manager
│   │
│   └── sms/
│       ├── class-karen-sms-adapter.php        # SMS adapter base
│       ├── class-karen-sms-melipayamak.php    # Melipayamak gateway
│       ├── class-karen-sms-kavenegar.php      # Kavenegar gateway
│       └── class-karen-sms-faraz.php          # Faraz gateway
│
├── assets/
│   ├── css/
│   │   └── admin.css                # Admin styles
│   └── js/
│       └── admin.js                 # Admin scripts
│
├── languages/
│   └── karen-customer-club.pot       # Translation file
│
└── .github/
    └── workflows/                   # CI/CD workflows (future)
```
