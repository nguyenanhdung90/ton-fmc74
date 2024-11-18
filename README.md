
- php 7.4
- laravel 7.3 
# For seeding data coin info 
- php artisan db:seed

# For running single command checking: deposit, withdraw
- php artisan ton:periodic_deposit
- php artisan ton:periodic_withdraw_ton
- php artisan ton:periodic_withdraw_jetton
- php artisan ton:periodic_withdraw_excess
  
# For example data test of wallet memo
- php artisan db:seed --class=WalletSeeder
- php artisan db:seed --class=WalletMemoSeeder
