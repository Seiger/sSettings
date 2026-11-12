import React from 'react';
import Layout from '@theme-original/DocItem/Layout';
import Link from '@docusaurus/Link';
import Translate from '@docusaurus/Translate';
import {useLocation} from '@docusaurus/router';
import useDocusaurusContext from '@docusaurus/useDocusaurusContext';
import useBaseUrl from '@docusaurus/useBaseUrl';
import {useAllDocsData, useDoc} from '@docusaurus/plugin-content-docs/client';
import {selectedLine, lineHref} from '../../../lib/routes.mjs';

/** Keep the branch warning in the site shell, never in canonical package Markdown. */
export default function DocLayout(props) {
    const {siteConfig, i18n} = useDocusaurusContext();
    const registry = siteConfig.customFields.documentation;
    const {pathname} = useLocation();
    const allDocs = useAllDocsData();
    const selected = selectedLine(pathname, registry);
    const current = registry.lines.find((line) => line.status === 'current');
    const {metadata} = useDoc();
    const coverUrl = useBaseUrl(`/img/${selected.id}-cover.png`);
    const releasesUrl = useBaseUrl('/releases/');
    return <>
        {selected.status !== 'current' && <aside className="docs-legacy" aria-label="Documentation version">
            <strong>sSettings {selected.label}</strong>{' — '}
            <Translate id="docs.legacy.message">You are reading an older documentation line.</Translate>{' '}
            <Link to={lineHref(current, pathname, allDocs, i18n.currentLocale, registry)}>
                <Translate id="docs.legacy.current">Read current documentation</Translate> ({current.label})
            </Link>
        </aside>}
        {selected.cover && metadata.slug === '/' && <div className="docs-overview-cover">
            <img src={coverUrl} width="1440" height="548"
                alt="sSettings manager with project settings and configuration tabs" />
            <Link to={releasesUrl}>
                <img src="https://img.shields.io/badge/PHP_supported-8.4_%7C_8.5-777BB4?logo=php&logoColor=white"
                    alt="PHP supported: 8.4 and 8.5" />
            </Link>
        </div>}
        <Layout {...props} />
    </>;
}
