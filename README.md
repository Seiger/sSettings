# sSettings documentation site

This branch holds the Docusaurus site for [sSettings](https://seiger.github.io/sSettings/).
The package branches hold the guides that dDocs and the website read.

- `2.x` is the current line at `/sSettings/`. Its guides live in `docs/<locale>/`.
  The Ukrainian directory is `docs/ua/` and the site's Ukrainian locale is `uk`.
- `1.x` is archived at `/sSettings/1.x/`. Its English guides are used as a
  fallback when that line has no translation.
- `docs.config.json` lists the branches, site languages and current line.

The [sCommerce documentation branch](https://github.com/Seiger/sCommerce/tree/docs)
provided the site structure, version menu and Pages workflow. The build reads
each line from its own branch and assembles a single Pages artifact.

## Local preview

Use Node.js 22 or newer. Run these commands from this branch's working directory.
Replace both paths with the corresponding local package checkouts.

```sh
npm ci
npm test
npm run docs:prepare -- "2.x=/path/to/sSettings-2x" "1.x=/path/to/sSettings-1x"
npm run build
npm run serve
```

Open `http://127.0.0.1:3000/sSettings/`. `.generated/` contains build views,
including links to the Markdown directories and the cover from `2.x`.

## GitHub Pages

Set the repository's Pages source to **GitHub Actions**. For the first
deployment, publish the documentation source commits in `1.x` and `2.x` before
publishing the first `docs` commit. The push to `docs` starts the build with
both sources in place.

Later, changes to the guides trigger the same reusable workflow from their
package branch. A push to `docs` rebuilds the site when its configuration or
theme changes. The workflow can also be run manually from `docs`.
