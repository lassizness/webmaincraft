# HiveTech SEO and indexing

## Public indexable routes

The frontend explicitly allows indexing only for public content with stable
search value:

- `/`
- `/news`
- published `/news/<slug>`
- `/servers`
- `/shop` while the shop feature is enabled
- `/vote`
- published `/page/<slug>`
- `/ratings` only while `ratings.public_enabled=1`

All other frontend routes send:

```http
X-Robots-Tag: noindex, nofollow, noarchive
```

and the HTML layout emits an equivalent robots meta tag.

Public player profiles are deliberately not indexed. They depend on each
player's privacy settings and are not part of the sitemap.

The admin layout is always `noindex,nofollow,noarchive`.

## robots.txt

Generated dynamically at:

```text
/robots.txt
```

It blocks private/internal areas and advertises:

```text
Sitemap: https://hivegrid.online/sitemap.xml
```

## sitemap.xml

Generated dynamically at:

```text
/sitemap.xml
```

The sitemap contains:

- public top-level pages;
- every published CMS page;
- every published news item;
- ratings only when the public ratings feature is enabled.

Localized URLs use the stable query parameter:

```text
?lang=ru
?lang=en
?lang=de
```

The language bootstrap recognizes this parameter without writing a cookie.
Sitemap entries and page heads emit matching `hreflang` values.

For translated CMS pages and news, only languages that actually have a stored
translation are advertised. Missing translations canonicalize to an available
language instead of creating duplicate fallback pages.

## Canonical and social metadata

Indexable pages emit:

- canonical URL;
- alternate `hreflang` links;
- localized title and description;
- Open Graph metadata;
- Twitter card metadata;
- `WebSite` and `Organization` schema.org JSON-LD.

News items additionally emit `NewsArticle` structured data.

## Favicon

The site ships:

- `/favicon.ico` with classic small favicon sizes;
- `/favicon-192.png` for modern browsers, Apple touch use and search engines;
- `/site.webmanifest`.

The 192×192 icon is large enough for search-engine favicon requirements and
is used as the default organization/social logo.

## Search engine ownership verification

Optional production environment variables:

```text
GOOGLE_SITE_VERIFICATION=
YANDEX_SITE_VERIFICATION=
BING_SITE_VERIFICATION=
```

When set, matching verification meta tags are rendered automatically.

Do not commit verification tokens into the repository.

## Deployment checks

After deployment:

```bash
curl -I https://hivegrid.online/
curl -I https://hivegrid.online/login
curl -sS https://hivegrid.online/robots.txt
curl -sS https://hivegrid.online/sitemap.xml | head -n 30
curl -I https://hivegrid.online/favicon.ico
curl -I https://hivegrid.online/favicon-192.png
```

Expected behavior:

- public pages return no `X-Robots-Tag: noindex`;
- account/auth/support/internal pages return `X-Robots-Tag: noindex, nofollow, noarchive`;
- robots and sitemap return HTTP 200;
- favicon assets return HTTP 200 with static caching.

After validation, submit `https://hivegrid.online/sitemap.xml` in Google
Search Console, Yandex Webmaster and Bing Webmaster Tools. Ownership can be
verified using the environment variables above.
