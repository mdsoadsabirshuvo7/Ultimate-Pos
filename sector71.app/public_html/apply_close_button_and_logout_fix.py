import os

files = [
    'public/sw.js',
    'public/js/offline-sync.js',
    'resources/views/layouts/partials/javascripts.blade.php'
]

# Read contents
contents = {}
for f in files:
    with open(f, 'r', encoding='utf-8') as file:
        contents[f] = file.read()

# 1. Add Close Widget Button HTML next to toggle-autohide
btn_orig = '<button data-sync-action="toggle-autohide" style="flex:1; padding:4px 6px; border:0; border-radius:6px; cursor:pointer; font-size:11px;">\' + modeLabel + \'</button>'
btn_fixed = '<button data-sync-action="toggle-autohide" style="flex:1; padding:4px 6px; border:0; border-radius:6px; cursor:pointer; font-size:11px;">\' + modeLabel + \'</button>\' +\n      \'<button data-sync-action="close-widget" style="margin-left:auto; padding:3px 6px; border:0; border-radius:6px; cursor:pointer; font-size:11px; background:#4b5563; color:#fff;">Close</button>'
contents['public/js/offline-sync.js'] = contents['public/js/offline-sync.js'].replace(btn_orig, btn_fixed)

# 2. Map close-widget action
action_orig = "if (action === 'toggle-autohide') {"
action_fixed = """if (action === 'close-widget') {
      var w = document.getElementById('s71-sync-widget');
      if (w) w.style.display = 'none';
      try { syncState.collapsed = true; } catch(e) {}
      return;
    }

    if (action === 'toggle-autohide') {"""
contents['public/js/offline-sync.js'] = contents['public/js/offline-sync.js'].replace(action_orig, action_fixed)

# 3. Fix Logout Bug (Bypass locks)
logout_orig = "postToSWWithRetry({ type: 'CLEAR_OFFLINE_DATA' }, SW_POST_MAX_ATTEMPTS);"
logout_fixed = """if (navigator.serviceWorker) {
        navigator.serviceWorker.getRegistrations().then(function(rs) {
          for (var i = 0; i < rs.length; i++) {
            rs[i].unregister();
          }
        });
      }
      if (window.caches) {
        caches.keys().then(function(keys) {
          keys.forEach(function(key) { caches.delete(key); });
        });
      }
      postToSWWithRetry({ type: 'CLEAR_OFFLINE_DATA' }, SW_POST_MAX_ATTEMPTS);"""
contents['public/js/offline-sync.js'] = contents['public/js/offline-sync.js'].replace(logout_orig, logout_fixed)

# 4. Bump sw.js and blade asset version to force update
contents['public/sw.js'] = contents['public/sw.js'].replace("'s71-sw-v6'", "'s71-sw-v7'")
contents['resources/views/layouts/partials/javascripts.blade.php'] = contents['resources/views/layouts/partials/javascripts.blade.php'].replace("pwa=22", "pwa=23")

# Write contents
for f in files:
    with open(f, 'w', encoding='utf-8') as file:
        file.write(contents[f])

print('Patched successfully.')
