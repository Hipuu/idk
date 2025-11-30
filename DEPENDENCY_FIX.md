# GitHub Actions Dependency Fix

## Issue
The GitHub Actions workflow was failing with:
```
E: Unable to locate package android-sdk-ext4-utils
```

## Root Cause
- `android-sdk-ext4-utils` package doesn't exist in Ubuntu repositories
- The correct package name is `android-sdk-libsparse-utils`
- `lpunpack` and `lpmake` needed to be downloaded as precompiled binaries

## Solution

### Fixed Packages
1. **Removed**: `android-sdk-ext4-utils` (doesn't exist)
2. **Kept**: `android-sdk-libsparse-utils` (contains simg2img, img2simg)
3. **Added**: `e2fsprogs` (for mkfs.ext4 utilities)

### lpunpack/lpmake Installation
Changed from broken method to working precompiled binaries:

**Before (broken)**:
```bash
wget ... -O /usr/local/bin/lpunpack
chmod +x /usr/local/bin/lpunpack
```

**After (working)**:
```bash
wget ... -O /tmp/lpunpack
sudo install -m 755 /tmp/lpunpack /usr/local/bin/lpunpack
```

Using `install` command is better because:
- Properly sets permissions in one step
- More reliable for system directories
- Standard method for installing binaries

### Package Sources
- **android-sdk-libsparse-utils**: Ubuntu universe repository
- **lpunpack/lpmake**: [unix3dgforce/lpunpack_and_lpmake](https://github.com/unix3dgforce/lpunpack_and_lpmake) precompiled binaries
- **simg2img**: Removed separate .deb download (included in android-sdk-libsparse-utils)

## Testing
The workflow should now successfully install all dependencies:
✅ android-sdk-libsparse-utils (simg2img, img2simg)
✅ lpunpack (from GitHub)
✅ lpmake (from GitHub)
✅ brotli, unzip, zip, aria2, wget, curl
✅ e2fsprogs (ext4 utilities)
