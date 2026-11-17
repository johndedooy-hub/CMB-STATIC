(function(){'use strict';
const max={menu:16,slides:12,tiles:24,tickers:15};let next=10000;
document.querySelectorAll('[data-add]').forEach(button=>{
 button.addEventListener('click',()=>{
  const group=button.dataset.add,wrapper=document.getElementById(group+'Rows'),source=document.getElementById('template-'+group);
  if(!wrapper||!source)return;
  if(wrapper.children.length>=max[group]){alert('Maximum bereikt: '+max[group]+' onderdelen.');return;}
  const html=source.innerHTML.replaceAll('__N__',String(next++));
  wrapper.insertAdjacentHTML('beforeend',html);
  wrapper.lastElementChild?.scrollIntoView({behavior:'smooth',block:'nearest'});
 });
});
document.addEventListener('click',e=>{
 const remove=e.target.closest('.remove-item');if(!remove)return;
 const row=remove.closest('.repeat-item');if(!row)return;
 if(confirm('Dit onderdeel verwijderen? Vergeet niet op Alles opslaan te drukken.'))row.remove();
});
const form=document.getElementById('mainForm');if(form){form.addEventListener('submit',e=>{
 const limit=Number(document.querySelector('input[name="layout[slide_interval]"]')?.value||8);
 if(limit<3||limit>60){alert('De slide-wisseltijd moet tussen 3 en 60 seconden liggen.');e.preventDefault();}
 });}
})();
