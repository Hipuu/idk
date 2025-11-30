#!/usr/bin/env python3
"""
Extract ROM metadata (codename, version) from ROM files
"""
import os
import sys
import zipfile
import re
import json
from pathlib import Path


def extract_build_prop(rom_path: str) -> dict:
    """Extract build.prop from ROM zip file."""
    build_prop = {}
    
    try:
        with zipfile.ZipFile(rom_path, 'r') as zip_ref:
            # Try different possible locations for build.prop
            possible_paths = [
                'system/build.prop',
                'system/system/build.prop',
                'META-INF/com/google/android/updater-script',
            ]
            
            build_prop_content = None
            for path in possible_paths:
                try:
                    build_prop_content = zip_ref.read(path).decode('utf-8', errors='ignore')
                    if 'ro.build' in build_prop_content or 'ro.product' in build_prop_content:
                        break
                except KeyError:
                    continue
            
            if not build_prop_content:
                print("Warning: Could not find build.prop in ROM", file=sys.stderr)
                return {}
            
            # Parse build.prop
            for line in build_prop_content.split('\n'):
                line = line.strip()
                if line and not line.startswith('#') and '=' in line:
                    key, value = line.split('=', 1)
                    build_prop[key.strip()] = value.strip()
    
    except Exception as e:
        print(f"Error extracting build.prop: {e}", file=sys.stderr)
    
    return build_prop


def get_rom_metadata(rom_path: str) -> dict:
    """Extract ROM metadata including codename and version."""
    build_prop = extract_build_prop(rom_path)
    
    # Extract codename
    codename = (
        build_prop.get('ro.product.device') or
        build_prop.get('ro.product.name') or
        build_prop.get('ro.build.product') or
        'unknown'
    )
    
    # Extract version
    version = (
        build_prop.get('ro.build.version.incremental') or
        build_prop.get('ro.build.id') or
        build_prop.get('ro.build.display.id') or
        'unknown'
    )
    
    # Try to extract more detailed version info
    display_id = build_prop.get('ro.build.display.id', '')
    
    # Look for version patterns like "16.0.0.205" in display ID
    version_match = re.search(r'(\d+\.\d+\.\d+\.\d+)', display_id)
    if version_match:
        version = version_match.group(1)
    else:
        # Try other patterns
        version_match = re.search(r'(\d+\.\d+\.\d+)', display_id)
        if version_match:
            version = version_match.group(1)
    
    # Additional metadata
    metadata = {
        'codename': codename,
        'version': version,
        'android_version': build_prop.get('ro.build.version.release', 'unknown'),
        'sdk_version': build_prop.get('ro.build.version.sdk', 'unknown'),
        'build_date': build_prop.get('ro.build.date', 'unknown'),
        'fingerprint': build_prop.get('ro.build.fingerprint', 'unknown'),
    }
    
    return metadata


def generate_filename(metadata: dict, rom_type: str) -> str:
    """Generate filename in format: codename-version-romtype.zip"""
    codename = metadata.get('codename', 'unknown')
    version = metadata.get('version', 'unknown')
    
    # Sanitize filename
    codename = re.sub(r'[^\w\-.]', '_', codename)
    version = re.sub(r'[^\w\-.]', '_', version)
    
    filename = f"{codename}-{version}-{rom_type}.zip"
    return filename


def main():
    if len(sys.argv) < 3:
        print("Usage: extract_rom_info.py <rom_path> <rom_type>")
        sys.exit(1)
    
    rom_path = sys.argv[1]
    rom_type = sys.argv[2]
    
    if not os.path.exists(rom_path):
        print(f"Error: ROM file not found: {rom_path}", file=sys.stderr)
        sys.exit(1)
    
    # Extract metadata
    metadata = get_rom_metadata(rom_path)
    
    # Generate filename
    filename = generate_filename(metadata, rom_type)
    
    # Output as JSON
    output = {
        'metadata': metadata,
        'filename': filename
    }
    
    print(json.dumps(output, indent=2))


if __name__ == '__main__':
    main()
