# HiveTech illustrated achievement medals v2

The UI displays illustrated WebP badges for every one of the 91 seeded achievements.
Each file is identified by the database achievement code, not by its numeric ID.
Legacy SVG icons remain the fallback when an image is absent.

## Deployment

1. Deploy the code in this branch into /opt/hivetech/portal.
2. Copy hivetech_badges_v2_assets.zip (separate handoff artifact) onto mc-edge.
3. Extract the 91 images to /opt/hivetech/portal/frontend/web/images/achievements/v2.
4. Verify https://hivegrid.online/account/achievements and admin achievement list.

Example when project root is /opt/hivetech/portal:

    mkdir -p frontend/web/images/achievements/v2
    unzip -j -o /tmp/hivetech_badges_v2_assets.zip 'images/achievements/v2/*.webp' -d frontend/web/images/achievements/v2
    find frontend/web/images/achievements/v2 -maxdepth 1 -name '*.webp' | wc -l

No database migration or Minecraft mod rebuild is needed. Existing unlocked/locked states remain unchanged.
Secret achievements continue to use a question-mark SVG until unlocked.
Images are also shown in the dashboard and admin achievement preview.

Assets are shipped separately because GitHub connector cannot transfer local binary image files.
