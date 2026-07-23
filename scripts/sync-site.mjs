// Mirror src/site -> build/site (templates, snippets, blueprints, controllers,
// config, models, …). CodeKit does this live while you work; this script is the
// no-CodeKit fallback used by `npm run build`.
import { cpSync, mkdirSync } from 'node:fs';

const from = 'src/site';
const to   = 'build/site';

mkdirSync(to, { recursive: true });
cpSync(from, to, { recursive: true });
console.log(`synced ${from} -> ${to}`);
