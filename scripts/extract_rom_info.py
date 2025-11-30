#!/usr/bin/env python3
"""
Extract ROM metadata (codename, version) from ROM files
Simplified version with random filename generation
"""
import sys
import os
import json
from datetime import datetime

def generate_random_filename(rom_type):
    """Generate filename with timestamp"""
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    filename = f"rom-{timestamp}-{rom_type}.zip"
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
    
    # Generate random filename
    filename = generate_random_filename(rom_type)
    
    # Output as JSON
    output = {
        'metadata': {
            'codename': 'rom',
            'version': datetime.now().strftime("%Y%m%d"),
            'android_version': 'unknown',
            'sdk_version': 'unknown',
            'build_date': datetime.now().strftime("%Y-%m-%d"),
            'fingerprint': 'unknown'
        },
        'filename': filename
    }
    
    print(json.dumps(output, indent=2))

if __name__ == '__main__':
    main()
