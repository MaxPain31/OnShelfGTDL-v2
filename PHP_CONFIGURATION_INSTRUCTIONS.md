# PHP Upload Size Configuration Instructions

## For WAMP64 Users

Your PHP configuration file is located at: `C:\wamp64\bin\php\php8.4.0\php.ini`

### Steps to Fix Large File Upload Issue:

1. **Open php.ini file:**
   - Navigate to: `C:\wamp64\bin\php\php8.4.0\php.ini`
   - Right-click and select "Open with" → Choose a text editor (Notepad++, VS Code, etc.)
   - **Important:** You may need to run your text editor as Administrator

2. **Find and modify these settings:**
   
   Search for these lines (use Ctrl+F):
   
   ```
   upload_max_filesize = 2M
   post_max_size = 8M
   max_execution_time = 30
   max_input_time = 60
   memory_limit = 128M
   ```
   
   Change them to:
   
   ```
   upload_max_filesize = 100M
   post_max_size = 100M
   max_execution_time = 300
   max_input_time = 300
   memory_limit = 256M
   ```

3. **Save the file**

4. **Restart WAMP Server:**
   - Click on the WAMP icon in the system tray
   - Click "Restart All Services"
   - Or stop and start Apache and MySQL services

5. **Verify the changes:**
   - Create a PHP file with: `<?php phpinfo(); ?>`
   - Check that the values are updated

## Alternative: Using .htaccess (if mod_php is enabled)

I've already added the configuration to `public/.htaccess`, but this may not work with PHP-FPM.

If the .htaccess method doesn't work, you MUST modify php.ini as described above.

## Notes:

- `post_max_size` must be **equal to or greater than** `upload_max_filesize`
- After making changes, always restart your web server
- The values above (100M) should handle files up to ~100MB. Adjust if you need larger files.

