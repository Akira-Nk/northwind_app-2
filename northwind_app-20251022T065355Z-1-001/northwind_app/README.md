# Northwind Sales - Simple PHP App (Bootstrap 5)
Files included:
- connDB.php (use with MAMP default MySQL user/password)
- dbNorthwind.sql (database dump)
- index.php (sales form)
- process_order.php (inserts order + details)
- sales_list.php (summary with totals)
- sales_detail.php (order detail)

## How to run (MAMP / macOS / Windows with MAMP/XAMPP)
1. Place the `northwind_app` folder into your MAMP `htdocs` (or XAMPP `htdocs`) folder.
2. Import `dbNorthwind.sql` into your local MySQL (phpMyAdmin) — the dump creates `db_northwind`.
3. Edit `connDB.php` if your DB username/password are different.
   - Default in this project: host=localhost, db=db_northwind, user=root, pass=root, charset=utf8mb4
4. Start Apache + MySQL via MAMP/XAMPP.
5. Open browser: `http://localhost/northwind_app/index.php`

## Notes / Limitations
- `tb_orders` and `tb_orderdetails` in the provided dump do not use AUTO_INCREMENT; `process_order.php` uses MAX()+1 to generate IDs.
- The app demonstrates required screens: sales form, sales summary, detail view, with modal confirm.
- For production or multi-user environments, ID generation should use AUTO_INCREMENT and transactions should handle concurrency more robustly.

## References
Project requirements were taken from the provided PDF. See the original PDF for exact assignment rules.
