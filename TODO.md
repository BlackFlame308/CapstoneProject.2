Internal Server Error

Copy as Markdown
Illuminate\Contracts\Container\BindingResolutionException
vendor\laravel\framework\src\Illuminate\Container\Container.php:1127
Target class [role] does not exist.

LARAVEL
13.5.0
PHP
8.4.12
UNHANDLED
CODE 0
500
GET
http://127.0.0.1:8000/dashboard

Exception trace
1 previous exception
54 vendor frames

Illuminate\Foundation\Application->handleRequest(object(Illuminate\Http\Request))
public\index.php:20

15
16// Bootstrap Laravel and handle the request...
17/** @var Application $app */
18$app = require_once __DIR__.'/../bootstrap/app.php';
19
20$app->handleRequest(Request::capture());
21
1 vendor frame

Previous exception
ReflectionException
Class "role" does not exist


Queries
1-2 of 2
mysql
select * from `users` where `id` = '019ddebd-af05-72f4-9f68-95790fc8257e' limit 1
24.99ms
mysql
select * from `roles` where `roles`.`id` = '019ddebd-ad50-7103-99b5-eee49a162644' limit 1
1.4ms
Headers
host
127.0.0.1:8000
connection
keep-alive
upgrade-insecure-requests
1
user-agent
Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36
accept
text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
sec-fetch-site
none
sec-fetch-mode
navigate
sec-fetch-user
?1
sec-fetch-dest
document
sec-ch-ua
"Google Chrome";v="147", "Not.A/Brand";v="8", "Chromium";v="147"
sec-ch-ua-mobile
?0
sec-ch-ua-platform
"Windows"
accept-encoding
gzip, deflate, br, zstd
accept-language
en-US,en;q=0.9
cookie
XSRF-TOKEN=eyJpdiI6IkNLZGJVWmI5OC9WT3Z3TVF1akQ1MEE9PSIsInZhbHVlIjoiUFdFY3VUbnBCb0F4cmFvWHVDck0xSXIxYVowbktWQjNVeEYzZG1zUEErUmN5T05XUVc4c0VsanE3U0FGeUJCaHNjaUxWc0RoUGdtU01MS1dFVUFSRFlxTGo5dklhejlXb2lLb1NlMUFMY1hkK2VkUGxPb0hYMk0xT0ZiVXVNZ3MiLCJtYWMiOiI4NjdiYTg4MTg5YjAwOTM5NjY0MGE3MDY4MmQ5YWNjN2E2NTJkMzg0NjIwMmI4Mjk2NmJjYmJhOWEzZTE0MTEyIiwidGFnIjoiIn0%3D; laravel-session=eyJpdiI6ImJIenNCWkptb0tTSlBPYXR2cWJyVHc9PSIsInZhbHVlIjoidGZEbmRia2p2QXhSck56ZDZVc1dBaEYyNTgrd3V1UHpXVWJDbnpKQWV1M0ZsODIzbDlEQ25SbTE0T2E1bDhjUzMyMkFYTUlCSHNNVUlueFh6MUR1WEU0WDYxa3RFSFBrcUpRTFExdXJXcjZIdnlmdU5UZVhFbXNUSTZFT2Q2bjEiLCJtYWMiOiJmZjBlYjAxY2E1ZTY0MDViMmMzNjBkMDZlY2ZiNzZjNmY1OTE2NzZjODI4NWQ2NGY0ZWQ1ZDJjNTExOTkyM2RiIiwidGFnIjoiIn0%3D
Body
// No request body
Routing
controller
App\Http\Controllers\DashboardController@index
route name
dashboard
middleware
web, auth, role:Captain|Encoder|Household
Routing parameters
// No routing parameters
# TODO - Fix Change Password Screen Hang Issue

## Completed Steps

- [x] 1. Analyzed codebase to understand authentication flow
- [x] 2. Fixed HandleInertiaRequests middleware - Added null-safety checks and try-catch for permission methods
- [x] 3. Fixed Layout.jsx component - Added defensive checks for user.permissions
- [x] 4. Fixed Dashboard/Index.jsx - Added defensive checks for permissions

## Summary

The "screen hang" issue was caused by JavaScript errors when accessing user permissions for Household users. The middleware was calling methods like canViewHouseholds() and hasPermission() which may fail silently on the PHP side but cause the React app to crash.

### Fixes Applied:

1. **HandleInertiaRequests.php (Backend)**:
   - Added try-catch block when building user data
   - Added method_exists() checks before calling user methods
   - Added fallback values when errors occur
   - Added must_change_password to the shared data

2. **Layout.jsx (Frontend)**:
   - Changed from `user?.permissions` to safe `permissions` variable with `?? {}` fallback
   - Updated all permission checks to use the safe `permissions` variable

3. **Dashboard/Index.jsx (Frontend)**:
   - Added safe user and permissions extraction
   - Changed to use the new `permissions` variable

## Next Steps

- [ ] Build the frontend assets (npm run build)
- [ ] Test the login flow with Household test credentials
- [ ] Verify no JavaScript errors in browser console
