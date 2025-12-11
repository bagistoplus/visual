import { createContentLoader } from 'vitepress';

export interface Post {
  title: string;
  url: string;
  date: {
    time: number;
    string: string;
  };
  excerpt: string | undefined;
  author: string;
  gravatar?: string;
  twitter?: string;
}

declare const data: Post[];
export { data };

function formatDate(raw: string): Post['date'] {
  const date = new Date(raw);
  date.setUTCHours(12);
  return {
    time: +date,
    string: date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    }),
  };
}

export default createContentLoader('blog/*.md', {
  excerpt: true,
  transform(raw): Post[] {
    return raw
      .filter(({ url }) => url !== '/blog/')
      .map(({ url, frontmatter, excerpt }) => ({
        title: frontmatter.title,
        url,
        excerpt: frontmatter.excerpt || excerpt,
        date: formatDate(frontmatter.date),
        author: frontmatter.author,
        gravatar: frontmatter.gravatar,
        twitter: frontmatter.twitter,
      }))
      .sort((a, b) => b.date.time - a.date.time);
  },
});
