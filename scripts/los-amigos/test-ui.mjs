import fs from 'node:fs';import vm from 'node:vm';import assert from 'node:assert/strict';
const source=fs.readFileSync(new URL('../../los-amigos/control/conectado.html',import.meta.url),'utf8').split('<script>')[1].split('</script>')[0];
const names=['previous','next','today','results','choose','input','dialog','cancel','form','connection'];
const els=Object.fromEntries(names.map(n=>[n,{innerHTML:'',textContent:'',value:'',handlers:{},setAttribute(){},addEventListener(e,f){this.handlers[e]=f},showModal(){this.open=true},close(){this.open=false},reportValidity(){return true}}]));
const pending=[];const context={document:{hidden:false,addEventListener(){},getElementById:()=>({querySelector:q=>els[q.match(/"(.*?)"/)[1]]})},Intl,Date,AbortController,setInterval(){},fetch:(url,opts)=>new Promise(resolve=>{assert.equal(opts.cache,'no-store');pending.push({url,resolve})})};
vm.runInNewContext(source,context);
const tick=()=>new Promise(r=>setImmediate(r));
function response(i,stays,status=200){const {url,resolve}=pending[i];resolve({ok:status===200,json:async()=>status===200?{date:new URL(url,'https://test.invalid').searchParams.get('date'),stays}:{error:'source_unavailable'}})}
// A slow response for a previous date must not replace the current selection.
els.next.handlers.click();response(1,[]);await tick();assert(els.results.innerHTML.includes('No hay huéspedes'));response(0,[{cabin:'Old',arrival:'2000-01-01',departure:'2100-01-01'}]);await tick();assert(!els.results.innerHTML.includes('Old'));
els.today.handlers.click();const day=new URL(pending[2].url,'https://test.invalid').searchParams.get('date');response(2,[{cabin:'<script>demo</script>',name:'<img src=x onerror=alert(1)>',people:null,arrival:day,departure:'2099-01-01'}]);await tick();assert(!els.results.innerHTML.includes('<script>'));assert(!els.results.innerHTML.includes('<img'));assert(els.results.innerHTML.includes('POR CONFIRMAR'));
els.next.handlers.click();response(3,[],502);await tick();assert(els.results.innerHTML.includes('No fue posible'));assert(!els.results.innerHTML.includes('No hay huéspedes'));
console.log('PASS UI: stale responses, unknown counts, text escaping, network error vs empty results');
