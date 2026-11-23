<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#0b1120">
  <title>TaskFlow — Smart Task Manager</title>
  <link rel="stylesheet" href="assets/style.css">
  <script src="assets/app.js" defer></script>
</head>
<body>
<div class="shell">
 <aside class="sidebar">
  <div class="logo"><span class="logo-icon">✓</span><div>TaskFlow <small>PERSONAL WORKSPACE</small></div></div>
  <div class="menu-label">WORKSPACE</div>
  <nav aria-label="Main navigation">
   <button class="nav-link active" data-view="all">▦ <span>All Tasks</span></button>
   <button class="nav-link" data-view="today">◷ <span>Due Today</span></button>
   <button class="nav-link" data-view="upcoming">▤ <span>Upcoming</span></button>
   <button class="nav-link" data-view="done">✓ <span>Completed</span></button>
  </nav>
  <div class="side-footer"><div class="avatar">AS</div><div><strong>Personal Workspace</strong><small>Local-first task management</small></div></div>
 </aside>
 <main class="main">
  <header class="topbar"><span class="breadcrumb">Workspace <b>/</b> <span id="crumb">Overview</span></span><div class="top-right"><span class="live-dot"></span> Local workspace</div></header>
  <section class="content">
   <div class="hero"><div><p class="eyebrow">YOUR PRODUCTIVITY HUB</p><h1>Make progress, <em>every day.</em></h1><p class="subtitle">Keep your priorities clear and your work moving forward.</p></div><button id="addTask" class="btn primary">＋ New Task</button></div>
   <div class="stats">
    <div class="stat"><span class="stat-top">Total Tasks <span class="stat-icon blue">▦</span></span><strong id="statTotal">0</strong><small>Everything on your board</small></div>
    <div class="stat"><span class="stat-top">In Progress <span class="stat-icon purple">◴</span></span><strong id="statProgress">0</strong><small>Currently in motion</small></div>
    <div class="stat"><span class="stat-top">Completed <span class="stat-icon green">✓</span></span><strong id="statDone">0</strong><small>Tasks you've finished</small></div>
    <div class="stat"><span class="stat-top">Overdue <span class="stat-icon orange">!</span></span><strong id="statOverdue">0</strong><small>Need your attention</small></div>
   </div>
   <section class="task-panel">
    <div class="panel-heading"><div><h2 id="sectionHeading">All Tasks</h2><p>Manage your workload in one place.</p></div><div class="progress-wrap"><span id="completionText">0% complete</span><div class="progress"><div id="completionBar"></div></div></div></div>
    <div class="toolbar"><label class="search">⌕ <input id="search" placeholder="Search tasks..." aria-label="Search tasks"></label><select id="filterPriority" aria-label="Filter priority"><option value="all">All priorities</option><option value="high">High priority</option><option value="medium">Medium priority</option><option value="low">Low priority</option></select><select id="sort" aria-label="Sort tasks"><option value="priority">Sort: Priority</option><option value="due">Sort: Due date</option><option value="recent">Sort: Newest</option><option value="title">Sort: A–Z</option></select></div>
    <div class="table-scroll"><table><thead><tr><th>TASK</th><th>PRIORITY</th><th>DUE DATE</th><th>STATUS</th><th class="actions">ACTIONS</th></tr></thead><tbody id="taskRows"></tbody></table></div>
    <div id="emptyState" class="empty" hidden><div class="empty-symbol">✦</div><h3>No tasks found</h3><p>Try another filter or create your first task.</p><button id="emptyAdd" class="btn outline">＋ Add a task</button></div>
    <footer class="panel-footer"><span id="taskCount">0 tasks</span><span>Built for better focus ✦</span></footer>
   </section>
   <div class="bottom-note">TASKFLOW <span>·</span> - Developed by merlylicious</div>
  </section>
 </main>
</div>
<dialog id="taskDialog" aria-labelledby="modalTitle"><form id="taskForm"><div class="dialog-head"><div><p class="eyebrow">TASK DETAILS</p><h2 id="modalTitle">Create a task</h2></div><button type="button" id="closeDialog" class="close" aria-label="Close">×</button></div><input type="hidden" id="taskId"><label>Title <span>*</span><input id="title" maxlength="120" required placeholder="What needs to get done?"></label><label>Description <textarea id="description" maxlength="1500" rows="3" placeholder="Add more details (optional)"></textarea></label><div class="form-grid"><label>Priority<select id="priority"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select></label><label>Status<select id="status"><option value="todo">To Do</option><option value="in_progress">In Progress</option><option value="done">Completed</option></select></label></div><label>Due Date <input id="dueDate" type="date"></label><div id="formError" role="alert" class="form-error" hidden></div><div class="dialog-actions"><button type="button" id="cancelDialog" class="btn outline">Cancel</button><button class="btn primary" id="saveTask" type="submit">Save Task</button></div></form></dialog>
<div id="toast" class="toast" role="status" aria-live="polite"></div>
</body></html>
