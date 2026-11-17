(function(){'use strict';
const el=document.getElementById('presetsData');if(!el)return;
let presets={};try{presets=JSON.parse(el.textContent||'{}')}catch(e){}
const controls=Array.from(document.querySelectorAll('[data-stream-index]'));
const status=document.getElementById('presetResult');
function values(i){const data={},root=controls[i];if(!root)return data;root.querySelectorAll('[data-stream-key]').forEach(f=>data[f.dataset.streamKey]=f.value);return data;}
function load(i,name){const item=presets[name];if(!item||!controls[i])return;const values={...item};if(!values.streamUrlAlt&&values.fallbackUrl)values.streamUrlAlt=values.fallbackUrl;controls[i].querySelectorAll('[data-stream-key]').forEach(f=>{if(Object.prototype.hasOwnProperty.call(values,f.dataset.streamKey))f.value=values[f.dataset.streamKey]||'';});status.textContent='Preset geladen; klik onderaan op Alles opslaan om te bewaren.';}
document.querySelectorAll('[data-load-preset]').forEach(btn=>btn.addEventListener('click',()=>{let i=Number(btn.dataset.loadPreset);load(i,controls[i].querySelector('.presetSelect').value);}));
document.querySelectorAll('[data-save-preset]').forEach(btn=>btn.addEventListener('click',async()=>{
 const i=Number(btn.dataset.savePreset),name=document.getElementById('newPresetName').value.trim();if(!name){status.textContent='Geef eerst een naam op.';return;}
 const csrf=document.querySelector('#mainForm [name=csrf]').value, fd=new FormData();fd.set('csrf',csrf);fd.set('name',name);
 Object.entries(values(i)).forEach(([k,v])=>fd.set('stream['+k+']',v));
 status.textContent='Bewaren…';try{const res=await fetch('preset.php',{method:'POST',body:fd,credentials:'same-origin'}),ans=await res.json();status.textContent=ans.message||'Onbekend antwoord';if(ans.ok){presets[name]=values(i);controls.forEach(root=>{const select=root.querySelector('.presetSelect');if(!Array.from(select.options).some(o=>o.value===name)){const op=document.createElement('option');op.value=name;op.textContent=name;select.appendChild(op);}});}}catch(e){status.textContent='Opslaan niet gelukt.';}
}));
})();
