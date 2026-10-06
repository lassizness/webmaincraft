#!/usr/bin/env python3
import json,sys,shutil,datetime
from pathlib import Path
if len(sys.argv)!=3:
    print('Usage: configure-gravit-textures.py /opt/hivetech/launchserver/LaunchServer.json https://hivetech.example')
    raise SystemExit(2)
p=Path(sys.argv[1]); base=sys.argv[2].rstrip('/'); stamp=datetime.datetime.now().strftime('%Y%m%d-%H%M%S'); backup=p.with_name(p.name+'.before-textures-'+stamp);shutil.copy2(p,backup)
d=json.loads(p.read_text(encoding='utf-8'));tp=d['auth']['std'].setdefault('textureProvider',{});tp.update({'type':'request','skinURL':base+'/texture/skin/%username%.png','cloakURL':base+'/texture/cloak/%username%.png'});p.write_text(json.dumps(d,ensure_ascii=False,indent=2)+'\n',encoding='utf-8');print('Backup:',backup);print('Skin:',tp['skinURL']);print('Cloak:',tp['cloakURL'])
