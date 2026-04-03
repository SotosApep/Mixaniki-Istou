Andreas Savvides AM 27410 (auth, candidate, modules)
Sotiris Hadjimichael AM 25233 (admin, database, includes, public)

### Get LAMP Running
1. You need a LAMP server: Linux, Apache, MySQL (or MariaDB), PHP.
   - On Windows, just download XAMPP or WAMP, it's easy.
   - On Linux, run `sudo apt install apache2 mysql-server php` or whatever for your distro.

2. Make sure PHP has `pdo` and `pdo_mysql` extensions enabled.

### Set Up the Database
1. Create a new database in MySQL/MariaDB. Call it something like `mixaniki_istou`.
2. Import the schema: run `mysql -u yourusername -p databasename < database/schema.sql`
   - Or use phpMyAdmin to import it.

3. If you want some sample data, import `database/seed.sql` too.

### Set Up the App
1. Put all the files in your web server's folder, like `/var/www/html/` or XAMPP's `htdocs/`.
2. Open `includes/db.php` and put in your database details (host, username, password, db name).
3. Go to `http://localhost/` in your browser and it should work.
