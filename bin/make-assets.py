#!/usr/bin/env python3
"""Build the wordpress.org icons/banners (.wordpress-org/) and the settings-page logo
(assets/logo.png) from Kevin's logo. Usage: bin/make-assets.py [logo.png]"""
import sys
from pathlib import Path
import numpy as np
from PIL import Image, ImageDraw, ImageFont, ImageFilter

root = Path(__file__).resolve().parents[1]
src = Image.open(sys.argv[1] if len(sys.argv) > 1 else '/root/work/flywp-health-assets/logo-original.png').convert('RGB')
fonts = Path('/root/work/font-geist-20260923/geist-src/geist-font/Geist/otf')
a = np.asarray(src).astype(int)
bg = np.median(np.concatenate([a[:40, :40].reshape(-1, 3), a[-40:, -40:].reshape(-1, 3)]), axis=0)
BG = tuple(int(v) for v in bg)
ys, xs = np.where(np.abs(a - bg).sum(axis=2) > 60)  # ring + pulse line
cx, cy = (xs.min() + xs.max()) / 2, (ys.min() + ys.max()) / 2
ring = max(xs.max() - xs.min(), ys.max() - ys.min())

def square(margin):
    side = ring * margin
    return src.crop((round(cx - side / 2), round(cy - side / 2), round(cx + side / 2), round(cy + side / 2)))

out = root / '.wordpress-org'
out.mkdir(exist_ok=True)
icon = square(1.06)
for s in (256, 128):
    icon.resize((s, s), Image.LANCZOS).save(out / f'icon-{s}x{s}.png', optimize=True)

def banner(w, h, name):
    im = Image.new('RGB', (w, h), BG)
    d = ImageDraw.Draw(im)
    ls = int(h * 0.64)
    logo = icon.resize((ls, ls), Image.LANCZOS)
    m = np.clip((np.abs(np.asarray(logo).astype(int) - np.array(BG)).sum(axis=2) - 12) / 40, 0, 1)
    mask = Image.fromarray((m * 255).astype('uint8')).filter(ImageFilter.GaussianBlur(0.6))
    lx = int(w * 0.075)
    im.paste(logo, (lx, (h - ls) // 2), mask)
    title = ImageFont.truetype(str(fonts / 'Geist-SemiBold.otf'), int(h * 0.19))
    sub = ImageFont.truetype(str(fonts / 'Geist-Regular.otf'), int(h * 0.076))
    tx = lx + ls + int(w * 0.045)
    tb = d.textbbox((0, 0), 'FlyWP Health', font=title)
    sb = d.textbbox((0, 0), 'Health check endpoint for uptime monitoring', font=sub)
    gap = int(h * 0.05)
    ty = (h - ((tb[3] - tb[1]) + gap + (sb[3] - sb[1]))) // 2
    d.text((tx, ty - tb[1]), 'FlyWP Health', font=title, fill=(255, 255, 255))
    d.text((tx, ty + (tb[3] - tb[1]) + gap - sb[1]), 'Health check endpoint for uptime monitoring', font=sub, fill=(167, 163, 201))
    im.save(out / name, optimize=True)

banner(1544, 500, 'banner-1544x500.png')
banner(772, 250, 'banner-772x250.png')

# Settings page: round dark disc, so the white pulse line stays visible on the light admin screen.
big = square(1.10).resize((320, 320), Image.LANCZOS)
disc = Image.new('L', (320, 320), 0)
ImageDraw.Draw(disc).ellipse((0, 0, 319, 319), fill=255)
big.putalpha(disc)
(root / 'assets').mkdir(exist_ok=True)
big.resize((80, 80), Image.LANCZOS).save(root / 'assets' / 'logo.png', optimize=True)
print('background', BG, 'ring', ring)
