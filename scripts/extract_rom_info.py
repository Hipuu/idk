#!/usr/bin/env python3
"""
Extract ROM metadata (codename, version) from ROM files
Supports both direct partition images and payload.bin format
"""
import sys
import zipfile
import tempfile
import shutil
import os
import json
import re
import subprocess

def parse_build_prop(content):
    """Parse build.prop content"""
    props = {}
    for line in content.split('\n'):
        line = line.strip()
        if line and not line.startswith('#') and '=' in line:
            key, value = line.split('=', 1)
            props[key.strip()] = value.strip()
    return props

def extract_build_prop_from_system_img(system_img_path):
    """Try to extract build.prop from system.img using various methods"""
    try:
        # Method 1: Try debugfs (doesn't require root)
        result = subprocess.run([
            'debugfs', '-R', 'cat /system/build.prop', system_img_path
        ], capture_output=True, text=True, timeout=30)
        
        if result.returncode == 0 and result.stdout and 'ro.build' in result.stdout:
            return result.stdout
    except:
        pass
    
    try:
        # Method 2: Try 7z extraction
        result = subprocess.run([
            '7z', 'e', '-so', system_img_path, 'system/build.prop'
        ], capture_output=True, text=True, timeout=30)
        
        if result.returncode == 0 and result.stdout and 'ro.build' in result.stdout:
            return result.stdout
    except:
        pass
    
    return None

def extract_rom_metadata(rom_path, rom_type):
    """Extract metadata from ROM file"""
    metadata = {
        'codename': 'unknown',
        'version': 'unknown',
        'android_version': 'unknown',
        'sdk_version': 'unknown',
        'build_date': 'unknown',
        'fingerprint': 'unknown'
    }
    
    temp_dir = tempfile.mkdtemp()
    try:
        # Extract ROM zip
        print("Extracting ROM ZIP...")
        with zipfile.ZipFile(rom_path, 'r') as zip_ref:
            zip_ref.extractall(temp_dir)
        
        # Check if payload.bin exists (OTA format)
        payload_path = os.path.join(temp_dir, 'payload.bin')
        if os.path.exists(payload_path):
            print("Detected payload.bin - extracting partitions...")
            payload_output = os.path.join(temp_dir, 'payload_extracted')
            os.makedirs(payload_output, exist_ok=True)
            
            # Extract payload.bin using payload-dumper-go
            try:
                subprocess.run([
                    'payload-dumper-go',
                    '-o', payload_output,
                    payload_path
                ], check=True, cwd=temp_dir, timeout=600)
                
                # Move extracted images to temp_dir
                for img_file in os.listdir(payload_output):
                    if img_file.endswith('.img'):
                        shutil.move(
                            os.path.join(payload_output, img_file),
                            os.path.join(temp_dir, img_file)
                        )
                
                print("Payload extraction complete!")
            except Exception as e:
                print(f"Error extracting payload: {e}", file=sys.stderr)
        
        # Try to extract build.prop from system.img
        system_img = os.path.join(temp_dir, 'system.img')
        if os.path.exists(system_img):
            print(f"Found system.img, extracting build.prop...")
            build_prop_content = extract_build_prop_from_system_img(system_img)
            if build_prop_content:
                props = parse_build_prop(build_prop_content)
                
                # Extract codename
                metadata['codename'] = (
                    props.get('ro.product.device') or
                    props.get('ro.product.name') or
                    props.get('ro.build.product') or
                    'unknown'
                )
                
                # Extract version
                display_id = props.get('ro.build.display.id', '')
                version_match = re.search(r'(\d+\.\d+\.\d+\.\d+)', display_id)
                if version_match:
                    metadata['version'] = version_match.group(1)
                else:
                    metadata['version'] = (
                        props.get('ro.build.version.incremental') or
                        props.get('ro.build.id') or
                        'unknown'
                    )
                
                metadata['android_version'] = props.get('ro.build.version.release', 'unknown')
                metadata['sdk_version'] = props.get('ro.build.version.sdk', 'unknown')
                metadata['build_date'] = props.get('ro.build.date', 'unknown')
                metadata['fingerprint'] = props.get('ro.build.fingerprint', 'unknown')
                
                print(f"Extracted metadata: {metadata['codename']} v{metadata['version']}")
        
        # Fallback: Look for build.prop in extracted files
        if metadata['codename'] == 'unknown':
            print("Searching for build.prop in extracted files...")
            for root, dirs, files in os.walk(temp_dir):
                if 'build.prop' in files:
                    build_prop_path = os.path.join(root, 'build.prop')
                    with open(build_prop_path, 'r', encoding='utf-8', errors='ignore') as f:
                        props = parse_build_prop(f.read())
                        metadata['codename'] = props.get('ro.product.device', 'unknown')
                        metadata['version'] = props.get('ro.build.version.incremental', 'unknown')
                        metadata['android_version'] = props.get('ro.build.version.release', 'unknown')
                    break
            else:
                print("Warning: Could not find build.prop in ROM")
    
    except Exception as e:
        print(f"Error extracting ROM metadata: {e}", file=sys.stderr)
    finally:
        shutil.rmtree(temp_dir, ignore_errors=True)
    
    return metadata

def generate_filename(metadata, rom_type):
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
    metadata = extract_rom_metadata(rom_path, rom_type)
    
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
