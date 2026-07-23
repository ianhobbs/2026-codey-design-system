// Seed build/content from the committed sample-content/ starter pages.
// build/content is gitignored (runtime data managed on the server), so a fresh
// clone has none — this gives it something to render. Safe: skips if content exists.
import { cpSync, existsSync, readdirSync } from 'node:fs';

const from = 'sample-content';
const to   = 'build/content';

if (existsSync(to) && readdirSync(to).length > 0) {
  console.log(`${to} already has content — skipping seed`);
  process.exit(0);
}
cpSync(from, to, { recursive: true });
console.log(`seeded ${to} from ${from}`);
