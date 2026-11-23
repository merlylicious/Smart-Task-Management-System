'use strict';
const $ = (s) => document.querySelector(s);
const state = { tasks: [], view: 'all' };
const today = () => { const d = new Date(); return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; };
const escapeHtml = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const labels = {todo:'To Do',in_progress:'In Progress',done:'Completed'};
const formatDate = (str) => str ? new Date(`${str}T12:00:00`).toLocaleDateString('en-MY',{day:'numeric',month:'short',year:'numeric'}) : 'No date';
let toastTimeout;
function toast(message, error=false) { const t=$('#toast'); t.textContent=message; t.className='toast visible'+(error?' error':''); clearTimeout(toastTimeout); toastTimeout=setTimeout(()=>t.className='toast',3000); }
async function request(method='GET',body=null) {
 const options={method,headers:{'Accept':'application/json'}};
 if (body) { options.headers['Content-Type']='application/json'; options.body=JSON.stringify(body); }
 const response=await fetch('api/tasks.php',options);
 const data=await response.json().catch(()=>({error:'Invalid server response.'}));
 if (!response.ok) throw new Error(data.error || `Request failed (${response.status}).`);
 return data;
}
async function reload() { try { state.tasks=(await request()).tasks; render(); } catch (e) { toast(e.message,true); } }
function render() {
 const tasks=state.tasks; const complete=tasks.filter(t=>t.status==='done').length;
 $('#statTotal').textContent=tasks.length;
 $('#statProgress').textContent=tasks.filter(t=>t.status==='in_progress').length;
 $('#statDone').textContent=complete;
 $('#statOverdue').textContent=tasks.filter(t=>t.due_date && t.due_date<today() && t.status!=='done').length;
 const percent=tasks.length?Math.round(complete/tasks.length*100):0;
 $('#completionText').textContent=percent+'% complete'; $('#completionBar').style.width=percent+'%';
 const search=$('#search').value.trim().toLowerCase(), priority=$('#filterPriority').value;
 const headings={all:'All Tasks',today:'Due Today',upcoming:'Upcoming Tasks',done:'Completed Tasks'};
 $('#sectionHeading').textContent=headings[state.view]; $('#crumb').textContent=headings[state.view];
 let filtered=tasks.filter(t=> {
  if(state.view==='today' && t.due_date!==today()) return false;
  if(state.view==='upcoming' && (!t.due_date || t.due_date<=today() || t.status==='done')) return false;
  if(state.view==='done' && t.status!=='done') return false;
  return (priority==='all'||priority===t.priority) && (`${t.title} ${t.description}`).toLowerCase().includes(search);
 });
 const sort=$('#sort').value;
 filtered.sort((a,b)=>{
  if(sort==='title') return a.title.localeCompare(b.title);
  if(sort==='recent') return Number(b.id)-Number(a.id);
  if(sort==='due') return (a.due_date||'9999').localeCompare(b.due_date||'9999');
  const p={high:0,medium:1,low:2};return p[a.priority]-p[b.priority] || (a.due_date||'9999').localeCompare(b.due_date||'9999');
 });
 $('#taskRows').innerHTML=filtered.map(t=>`<tr><td><div class="task-title">${escapeHtml(t.title)}</div><span class="task-desc">${escapeHtml(t.description || 'No description')}</span></td><td><span class="pill ${escapeHtml(t.priority)}">${escapeHtml(t.priority.toUpperCase())}</span></td><td class="${t.due_date && t.due_date<today() && t.status!=='done'?'overdue':''}">${escapeHtml(formatDate(t.due_date))}</td><td><span class="pill ${escapeHtml(t.status)}">${labels[t.status] || 'Unknown'}</span></td><td class="actions"><button class="action-btn" data-action="edit" data-id="${Number(t.id)}" aria-label="Edit ${escapeHtml(t.title)}">Edit</button><button class="action-btn danger" data-action="delete" data-id="${Number(t.id)}" aria-label="Delete ${escapeHtml(t.title)}">✕</button></td></tr>`).join('');
 $('#emptyState').hidden=filtered.length>0;$('#taskCount').textContent=`${filtered.length} task${filtered.length!==1?'s':''}`;
}
function openDialog(task=null) {
 $('#taskForm').reset();$('#formError').hidden=true;
 $('#taskId').value=task?.id||'';
 $('#title').value=task?.title||'';$('#description').value=task?.description||'';
 $('#priority').value=task?.priority||'medium';$('#status').value=task?.status||'todo';$('#dueDate').value=task?.due_date||'';
 $('#modalTitle').textContent=task?'Edit task':'Create a task';$('#saveTask').textContent=task?'Save Changes':'Create Task';
 $('#taskDialog').showModal();$('#title').focus();
}
$('#addTask').addEventListener('click',()=>openDialog());
$('#emptyAdd').addEventListener('click',()=>openDialog());
$('#closeDialog').addEventListener('click',()=>$('#taskDialog').close());
$('#cancelDialog').addEventListener('click',()=>$('#taskDialog').close());
$('#taskDialog').addEventListener('click',e=>{if(e.target===$('#taskDialog')) $('#taskDialog').close();});
$('#taskForm').addEventListener('submit',async e=>{
 e.preventDefault();const id=Number($('#taskId').value);
 const task={title:$('#title').value,description:$('#description').value,priority:$('#priority').value,status:$('#status').value,due_date:$('#dueDate').value||null};
 if(id)task.id=id;
 $('#saveTask').disabled=true;
 try{await request(id?'PUT':'POST',task);$('#taskDialog').close();toast(id?'Task updated successfully.':'Task created successfully.');await reload();}
 catch(err){$('#formError').hidden=false;$('#formError').textContent=err.message;}
 finally{$('#saveTask').disabled=false;}
});
$('#taskRows').addEventListener('click',async e=>{
 const btn=e.target.closest('[data-action]');if(!btn)return;
 const id=Number(btn.dataset.id);const task=state.tasks.find(t=>Number(t.id)===id);if(!task)return;
 if(btn.dataset.action==='edit'){openDialog(task);return;}
 if(!window.confirm(`Delete "${task.title}"? This cannot be undone.`))return;
 try{await request('DELETE',{id});toast('Task deleted.');await reload();}catch(err){toast(err.message,true);}
});
document.querySelectorAll('.nav-link').forEach(btn=>btn.addEventListener('click',()=>{
 document.querySelectorAll('.nav-link').forEach(b=>b.classList.toggle('active',b===btn));state.view=btn.dataset.view;render();
}));
['#search','#filterPriority','#sort'].forEach(s=>$(s).addEventListener(s==='#search'?'input':'change',render));
reload();
