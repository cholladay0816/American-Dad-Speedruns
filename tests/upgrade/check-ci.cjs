const fs = require('node:fs');
const {yaml} = require('../baseline/node_modules/playwright-core/lib/utilsBundle.js');
const w=yaml.parse(fs.readFileSync('.github/workflows/laravel.yml','utf8'));
if(w.permissions.contents!=='read'||w.jobs.regression.steps.length!==6)throw Error('Unexpected workflow boundaries');
for(const step of w.jobs.regression.steps)if(step.uses&&!/@[a-f0-9]{40}$/.test(step.uses))throw Error('Unpinned action');
console.log('Workflow YAML valid; read-only token; six steps; actions pinned to verified revisions.');
