import path from 'path';
import { writeFileSync } from 'fs';
import { Feed } from 'feed';
import { createContentLoader, type SiteConfig } from 'vitepress';

const baseUrl = 'https://visual.bagistoplus.com';

export async function genFeed(config: SiteConfig) {
  const feed = new Feed({
    title: 'Bagisto Visual Blog',
    description: 'Updates, tutorials, and insights from the Bagisto Visual team',
    id: baseUrl,
    link: baseUrl,
    language: 'en',
    image: `${baseUrl}/bagistoplus-visual-logo.png`,
    favicon: `${baseUrl}/favicon.ico`,
    copyright: 'Copyright © 2025 BagistoPlus',
  });

  const posts = await createContentLoader('blog/*.md', {
    excerpt: true,
    render: true,
  }).load();

  posts.sort((a, b) => +new Date(b.frontmatter.date) - +new Date(a.frontmatter.date));

  for (const { url, excerpt, frontmatter, html } of posts) {
    feed.addItem({
      title: frontmatter.title,
      id: `${baseUrl}${url}`,
      link: `${baseUrl}${url}`,
      description: excerpt,
      content: html,
      author: [
        {
          name: frontmatter.author,
          link: frontmatter.twitter ? `https://twitter.com/${frontmatter.twitter.replace('@', '')}` : undefined,
        },
      ],
      date: new Date(frontmatter.date),
    });
  }

  writeFileSync(path.join(config.outDir, 'feed.rss'), feed.rss2());
}
