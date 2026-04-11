import os
import sys

def patch_file(filepath):
    print(f'Patching {filepath}...')
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Common fixes
    content = content.replace(r'/^\/(login|logout|register|password|forgot-password|reset-password|two-factor|email\/verify)(\/|$)/i', r'/(?:\/|^)(login|logout|register|password|forgot-password|reset-password|two-factor|email\/verify)(?:\/|$|\?)/i')
    
    if filepath.endswith('sw.js'):
        content = content.replace(\"'s71-sw-v5'\", \"'s71-sw-v6'\")
        content = content.replace(\"return p === '/login';\", \"return p === '/login' || p.endsWith('/login');\")

    if filepath.endswith('offline-sync.js'):
        content = content.replace(r'return /^\/logout(\/|$)/i.test', r'return /(?:\/|^)logout(?:\/|$|\?)/i.test')
        content = content.replace(\"if (pathname === '/logout') {\", \"if (pathname === '/logout' || pathname.endsWith('/logout')) {\")

    if filepath.endswith('javascripts.blade.php'):
        content = content.replace('pwa=21', 'pwa=22')

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

patch_file('/home/u980949395/domains/sector71.app/public_html/public/sw.js')
patch_file('/home/u980949395/domains/sector71.app/public_html/public/js/offline-sync.js')
patch_file('/home/u980949395/domains/sector71.app/public_html/resources/views/layouts/partials/javascripts.blade.php')
print('Patched successfully!')
