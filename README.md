A comprehensive backend consist of:

1. A token based (Passport JWT) login system
2. Custom refresh token (no strategy used)
3. Spatie Roles and Permission (Role-based)
4. Database used: PostgreSQL
5. To run the project first need the run the following:
   a. create a database
   b. adjust .env file (re: database connection)
   c. composer install
   d. php artisan key:generate
   e. php artisan passport:keys
   f. php artisan migrate
   g. php artisan db:seed --class=RoleSeeder
   h. php artisan db:seed --class=UserSeeder
   f. php artisan serve
