import os

files = [
    'public/sw.js',
    'public/js/offline-sync.js',
    'resources/views/layouts/partials/javascripts.blade.php'
]

contents = {}
for f in files:
    with open(f, 'r', encoding='utf-8') as file:
        contents[f] = file.read()

old_re = r"/^\/(login|logout|register|password|forgot-password|reset-password|two-factor|email\/verify)(\/|$)/i"
new_re = r"/(?:\/|^)(login|logout|register|password|forgot-password|reset-password|two-factor|email\/verify)(?:\/|$|\?)/i"
contents['public/sw.js'] = contents['public/sw.js'].replace(old_re, new_re)
contents['public/js/offline-sync.js'] = contents['public/js/offline-sync.js'].replace(old_re, new_re)

contents['public/sw.js'] = contents['public/sw.js'].replace("'s71-sw-v5'", "'s71-sw-v6'")
contents['public/sw.js'] = contents['public/sw.js'].replace("return p === '/login';", "return p === '/login' || p.endsWith('/login');")

contents['public/js/offline-sync.js'] = contents['public/js/offline-sync.js'].replace(r"return /^\/logout(\/|$)/i.test", r"return /(?:\/|^)logout(?:\/|$|\?)/i.test")
contents['public/js/offline-sync.js'] = contents['public/js/offline-sync.js'].replace(r"if (pathname === '/logout') {", r"if (pathname === '/logout' || pathname.endsWith('/logout')) {")

contents['resources/views/layouts/partials/javascripts.blade.php'] = contents['resources/views/layouts/partials/javascripts.blade.php'].replace("pwa=21", "pwa=22")

for f in files:
    with open(f, 'w', encoding='utf-8') as file:
        file.write(contents[f])

print('Patched successfully.')
