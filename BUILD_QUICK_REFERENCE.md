# 🚀 Build & Deploy - Quick Reference Card

## ⚡ 30-Second Summary

```
LOCAL BUILD                    →    SERVER UPLOAD
─────────────────────────────────────────────────────────────
Run: npm run production       →    Upload: public/frontend/js/
Location: C:\shiab\dolbear-web-app     →    Upload: public/frontend/css/
                               →    Upload: public/admin/js/
                               →    Upload: mix-manifest.json
```

---

## 📦 Build Files Location

### On Your Local Computer (After Build):

```
C:\shiab\dolbear-web-app\public\
├── frontend\
│   ├── js\
│   │   ├── app.js              ⭐ (2.3 MB) ← FRONTEND JS
│   │   ├── app.js.gz           ⭐ (522 KB)
│   │   └── chunks-180\         ⭐ (20-30 files) ← COMPONENTS
│   └── css\
│       └── app.css             ⭐ (496 KB) ← FRONTEND CSS
├── admin\
│   └── js\
│       └── app.js              ⭐ (934 KB) ← ADMIN JS
└── mix-manifest.json           ⭐ (2 KB) ← ASSET MAP
```

### On Live Server (Upload To):

```
public_html\
├── frontend\
│   ├── js\
│   │   ├── app.js              ← From local: public/frontend/js/app.js
│   │   └── chunks-180\         ← From local: public/frontend/js/chunks-180/
│   └── css\
│       └── app.css             ← From local: public/frontend/css/app.css
├── admin\
│   └── js\
│       └── app.js              ← From local: public/admin/js/app.js
└── mix-manifest.json           ← From local: mix-manifest.json
```

---

## 🎯 3 Simple Steps

### Step 1: Build Locally (2 min)
```bash
# Open terminal/CMD
cd C:\shiab\dolbear-web-app

# Run production build
npm run production

# Wait for "Webpack successfully compiled"
```

### Step 2: Upload Files (5 min)
```bash
# Via cPanel File Manager or FTP:

Upload these 4 items:
1. public/frontend/js/     (entire folder)
2. public/frontend/css/     (entire folder)
3. public/admin/js/         (entire folder)
4. mix-manifest.json        (single file)

To this location on server:
public_html/
```

### Step 3: Test (1 min)
```bash
Frontend: https://yourdomain.com
Admin:    https://yourdomain.com/admin/login
```

---

## 📊 What Each File Does

| File | Size | Purpose | Upload? |
|------|------|---------|---------|
| `frontend/js/app.js` | 2.3 MB | Main Vue.js app | ✅ Yes |
| `frontend/js/chunks-180/` | Various | Vue components | ✅ Yes |
| `frontend/css/app.css` | 496 KB | All styles | ✅ Yes |
| `admin/js/app.js` | 934 KB | Admin panel | ✅ Yes |
| `mix-manifest.json` | 2 KB | Asset mapping | ✅ Yes |

---

## 🔍 Verify Build Success

### Windows Command:
```cmd
dir "C:\shiab\dolbear-web-app\public\frontend\js\app.js"
```
**Expected:** ~2,344,030 bytes

### PowerShell:
```powershell
(Get-Item "C:\shiab\dolbear-web-app\public\frontend\js\app.js").Length
```
**Expected:** 2344030

### Check Chunks:
```cmd
dir "C:\shiab\dolbear-web-app\public\frontend\js\chunks-180" | find /c ".js"
```
**Expected:** 20-30 files

---

## ⚠️ Common Mistakes

### ❌ Don't Upload:
- `node_modules/` folder
- `vendor/` folder
- `.git/` folder
- `storage/` folder (except storage link)
- Source `resources/` folder

### ✅ Always Upload:
- `mix-manifest.json` (CRITICAL!)
- All compiled JS files
- All compiled CSS files
- All chunk files

---

## 🚨 Troubleshooting

### Problem: Vue.js Not Working
**Solution:**
```bash
1. Check mix-manifest.json exists on server
2. Check .env has: MIX_ASSET_URL=https://yourdomain.com
3. Clear browser cache (Ctrl+F5)
```

### Problem: 404 on JS Files
**Solution:**
```bash
1. Verify chunks-180/ folder uploaded
2. Check file permissions (644)
3. Re-upload mix-manifest.json
```

### Problem: Build Fails
**Solution:**
```bash
npm cache clean --force
rm -rf node_modules
npm install
npm run production
```

---

## 📝 Build Commands

| Command | Time | Output | Use For |
|---------|------|--------|---------|
| `npm run dev` | 30s | Large files | Development |
| `npm run production` | 2min | Small files | **Live server** ⭐ |
| `npm run watch` | Continuous | Auto-rebuild | Development |

---

## ✅ Pre-Upload Checklist

- [ ] Ran `npm run production`
- [ ] Build completed successfully
- [ ] `app.js` files exist
- [ ] `chunks-180/` has 20-30 files
- [ ] `mix-manifest.json` exists
- [ ] File sizes are correct (not 0 bytes)

---

## 🎯 Upload Methods

### Method 1: cPanel File Manager
```
1. Login to cPanel
2. File Manager → public_html
3. Upload ZIP of build folders
4. Extract
5. Verify files exist
```

### Method 2: FTP/FileZilla
```
1. Connect to server
2. Navigate to public_html/
3. Drag & drop:
   - frontend/js/
   - frontend/css/
   - admin/js/
   - mix-manifest.json
4. Wait for upload complete
```

### Method 3: Git (Advanced)
```
git add public/frontend/js/ public/frontend/css/ public/admin/js/ mix-manifest.json
git commit -m "Build assets"
git push origin main
```

---

## 🔧 When to Rebuild

### Rebuild When:
- ✅ Modified any `.vue` component
- ✅ Changed JS code
- ✅ Updated CSS files
- ✅ Added/removed npm packages
- ✅ Changed webpack.mix.js

### No Rebuild Needed:
- ❌ Modified PHP files
- ❌ Updated .env file
- ❌ Changed database
- ❌ Modified blade templates

---

## 📊 Time Estimates

| Task | Time |
|------|------|
| Build locally | 2 min |
| Compress files | 1 min |
| Upload to server | 3-5 min |
| Verify & test | 1 min |
| **Total** | **~8 min** |

---

## 🎯 Success Indicators

You'll know it worked when:

✅ Homepage loads without errors
✅ Browser console shows no Vue.js errors
✅ Network tab shows 200 OK for app.js
✅ Vue components render on page
✅ Admin panel loads
✅ No 404 errors for assets

---

## 📞 Quick Help

**Check browser console (F12):**
```javascript
// Should see:
Vue.js loaded ✅
App mounted ✅
No errors ✅
```

**Check Network tab:**
```
app.js           200 OK   (2.3 MB)
app.css          200 OK   (496 KB)
chunks/*.js      200 OK   (various)
```

---

## 💡 Pro Tips

1. **Build before lunch** → Upload while eating
2. **Use FTP for large files** → Faster than File Manager
3. **Keep mix-manifest.json** → Always upload it!
4. **Test in incognito** → Avoid cache issues
5. **Monitor build logs** → Catch errors early

---

## 🎉 Done!

**Result:** Your Vue.js app is live! 🚀

---

**Need More Details?**
- `BUILD_FILES_GUIDE.md` - Complete file details
- `BUILD_PROCESS_VISUAL.md` - Visual diagrams
- `CPANEL_QUICK_START.md` - cPanel deployment
- `DEPLOYMENT_GUIDE.md` - Full documentation

---

**Last Updated:** 2026-01-14
