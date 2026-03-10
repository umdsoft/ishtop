/**
 * SEO composable — updates document title and meta tags
 */

export function useSeo() {
  function setMeta(name, content) {
    let el = document.querySelector(`meta[name="${name}"]`) ||
             document.querySelector(`meta[property="${name}"]`);
    if (!el) {
      el = document.createElement('meta');
      if (name.startsWith('og:')) {
        el.setAttribute('property', name);
      } else {
        el.setAttribute('name', name);
      }
      document.head.appendChild(el);
    }
    el.setAttribute('content', content);
  }

  function set({ title, description, ogTitle, ogDescription, ogImage }) {
    if (title) document.title = title;
    if (description) setMeta('description', description);
    if (ogTitle) setMeta('og:title', ogTitle || title);
    if (ogDescription) setMeta('og:description', ogDescription || description);
    if (ogImage) setMeta('og:image', ogImage);
  }

  return { set };
}
