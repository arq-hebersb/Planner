<?php
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);
// Log errors to a file in the same folder (ensure writable)
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/rum_planner_error.log');

// /public_html/RUM_Tools/Planner/rum_planner.php
ob_start();
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
if (!isset($_SESSION['user_id'])) { header('Location: /login.php'); exit; }
header('Content-Type: text/html; charset=UTF-8');

$USER_NAME = $_SESSION['user_name'] ?? 'Heber';
?><!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RÜM Tools | RÜM Planner 5D</title>

  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    :root{
      --rum-primary:#2A6DB0;
      --rum-accent:#C46A4A;
      --rum-btn:#F6A868;

      --text-main:#0b1220;
      --text-muted:#4b5563;

      --glass: rgba(255,255,255,.65);
      --glass-2: rgba(255,255,255,.82);
      --glass-border: rgba(17,24,39,.08);

      --card: #ffffff;
      --card-2: #f8fafc;
      --line: rgba(17,24,39,0.12);
      --shadow: 0 20px 40px rgba(15,23,42,.12);

      --danger:#dc2626;
      --warn:#f59e0b;
      --ok:#16a34a;
      --info:#2563eb;
    }
.rum-planner-page *{ box-sizing: border-box; }
    html, body { height: 100%; }
.rum-planner-page{
      margin:0;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: radial-gradient(circle at top, #ffffff 0, #eef2f7 60%);
      color: var(--text-main);
      overflow-x:hidden;
    }
.rum-planner-page a{ color:inherit; text-decoration:none; }

    /* Stars */
    .stars-container{ position:fixed; inset:0; pointer-events:none; opacity:.12; }
    .stars-container::before, .stars-container::after{
      content:"";
      position:absolute; inset:-20%;
      background-image:
        radial-gradient(2px 2px at 20px 30px, rgba(255,255,255,.6), rgba(255,255,255,0)),
        radial-gradient(1px 1px at 150px 120px, rgba(255,255,255,.5), rgba(255,255,255,0)),
        radial-gradient(1px 1px at 90px 200px, rgba(255,255,255,.45), rgba(255,255,255,0)),
        radial-gradient(2px 2px at 240px 80px, rgba(255,255,255,.55), rgba(255,255,255,0)),
        radial-gradient(1px 1px at 330px 260px, rgba(255,255,255,.4), rgba(255,255,255,0)),
        radial-gradient(1px 1px at 420px 40px, rgba(255,255,255,.55), rgba(255,255,255,0));
      background-size: 520px 320px;
      animation: starMove 55s linear infinite;
      opacity:.55;
    }
    .stars-container::after{
      background-size: 720px 520px;
      animation-duration: 90s;
      opacity:.35;
    }
    @keyframes starMove{
      from{ transform: translate3d(0,0,0); }
      to{ transform: translate3d(120px,80px,0); }
    }

    .satellite{
      position:fixed;
      width:240px; height:240px;
      border-radius:50%;
      background: radial-gradient(circle at 30% 30%, rgba(0,191,255,.24), rgba(0,191,255,0) 60%),
                  radial-gradient(circle at 70% 70%, rgba(246,168,104,.18), rgba(246,168,104,0) 65%);
      filter: blur(0.2px);
      opacity:.32;
      pointer-events:none;
      animation: floaty 10s ease-in-out infinite;
      mix-blend-mode: screen;
    }
    .satellite-1{ top: -60px; right:-70px; }
    .satellite-2{ bottom:-70px; left:-70px; animation-duration: 12s; }
    @keyframes floaty{
      0%,100%{ transform: translateY(0) translateX(0); }
      50%{ transform: translateY(16px) translateX(-10px); }
    }

    .rum-shell{
      position:relative;
      max-width: 1500px;
      margin: 0 auto;
      padding: 22px 18px 90px;
      z-index:2;
      display:flex;
      flex-direction:column;
      gap:16px;
    }

    .nav-wrap{
      position: sticky;
      top: 0;
      z-index: 20;
      padding-top: 10px;
      backdrop-filter: blur(12px);
    }

    .nav-fallback{
      background: rgba(255,255,255,.92);
      border: 1px solid rgba(17,24,39,.10);
      border-radius: 16px;
      padding: 10px 14px;
      box-shadow: 0 16px 40px rgba(15,23,42,.12);
      display:flex; align-items:center; justify-content:space-between;
    }
    .nav-fallback .brand{
      display:flex; gap:10px; align-items:center;
    }
    .nav-fallback .logo{
      width:38px; height:38px; border-radius:12px;
      display:grid; place-items:center;
      font-weight:800;
      background: linear-gradient(135deg, rgba(59,130,246,.20), rgba(246,168,104,.18));
      border: 1px solid rgba(17,24,39,.10);
    }
    .nav-fallback .ch{ font-family:"Baloo 2"; font-size:18px; }
    .nav-fallback .right{
      display:flex; gap:10px; align-items:center; opacity:.9;
      font-size:14px;
    }

    .page-head{
      margin-top: 18px;
      display:flex; align-items:flex-end; justify-content:space-between;
      gap:16px;
      flex-wrap:wrap;
    }
    .page-head h1{
      margin:0;
      font-family:"Baloo 2";
      letter-spacing:.2px;
      font-size: 32px;
      line-height:1.1;
    }
    .page-head p{
      margin:6px 0 0;
      color: var(--text-muted);
      max-width: 900px;
      line-height:1.4;
      font-size:14px;
    }

    .chip{
      padding: 8px 10px;
      border-radius: 999px;
      border: 1px solid rgba(17,24,39,.12);
      background: rgba(255,255,255,.9);
      font-size: 12px;
      white-space:nowrap;
      color: #111827;
    }

    /* Cards layout */
    .grid{
      margin-top: 18px;
      display:grid;
      grid-template-columns: 1.2fr .8fr;
      gap: 14px;
      align-items:start;
    }
    @media (max-width: 1180px){
      .grid{ grid-template-columns: 1fr; }
    }

    .panel{
      border-radius: 18px;
      background: var(--card);
      border: 1px solid var(--line);
      box-shadow: var(--shadow);
      overflow:hidden;
      color: #111827;
    }
    .panel-header{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap:12px;
      padding: 14px 16px;
      background: #f8fafc;
      border-bottom: 1px solid rgba(15,23,42,.08);
    }
    .panel-header h2{
      margin:0;
      font-family:"Baloo 2";
      font-size: 18px;
      letter-spacing:.2px;
    }
    .panel-header p{
      margin:6px 0 0;
      font-size: 12px;
      color: #4b5563;
      line-height: 1.35;
    }
    .panel-body{ padding: 14px 16px; }

    .actions{
      display:flex;
      gap:8px;
      flex-wrap:wrap;
      justify-content:flex-end;
    }

    .btn{
      border: 1px solid rgba(15,23,42,.14);
      background: rgba(255,255,255,.9);
      color: #111827;
      padding: 9px 12px;
      border-radius: 12px;
      font-weight: 700;
      font-size: 12px;
      cursor:pointer;
      transition: transform .12s ease, background .12s ease, border-color .12s ease;
      user-select:none;
      white-space:nowrap;
    }
    .btn:hover{ transform: translateY(-1px); border-color: rgba(15,23,42,.22); background: #ffffff; }
    .btn:active{ transform: translateY(0); }
    .btn-primary{ background: rgba(42,109,176,.22); border-color: rgba(42,109,176,.38); }
    .btn-warn{ background: rgba(245,158,11,.18); border-color: rgba(245,158,11,.35); }
    .btn-danger{ background: rgba(220,38,38,.16); border-color: rgba(220,38,38,.35); }
    .btn-mini{ padding: 7px 10px; border-radius: 10px; font-size: 12px; font-weight: 700; }

    /* White cards inside */
    .cards{
      display:grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
    }
    @media (max-width: 900px){
      .cards{ grid-template-columns: 1fr; }
    }
    .card{
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: 16px;
      padding: 12px 12px;
      color: var(--text-main);
      box-shadow: 0 10px 30px rgba(0,0,0,.20);
      position:relative;
      overflow:hidden;
    }
    .card .k{
      font-size: 12px;
      color: var(--text-muted);
      font-weight: 700;
      margin-bottom: 6px;
    }
    .card .v{
      font-size: 22px;
      font-weight: 800;
      letter-spacing:.2px;
    }
    .card .s{
      margin-top: 8px;
      font-size: 12px;
      color: var(--text-main);
      font-weight: 700;
      opacity:.9;
    }

    .row{
      display:flex;
      gap:10px;
      flex-wrap:wrap;
      align-items:center;
      justify-content:space-between;
    }
    .muted{ color: rgba(244,242,237,.70); font-size:12px; }

    .filters{
      display:flex; gap:10px; flex-wrap:wrap; align-items:center;
      padding: 12px 16px;
      border-bottom: 1px solid rgba(255,255,255,.12);
      background: rgba(255,255,255,.03);
    }
    select, input[type="text"], input[type="number"], textarea{
      border-radius: 12px;
      border: 1px solid rgba(17,24,39,0.14);
      padding: 10px 10px;
      font-size: 12px;
      background: rgba(255,255,255,.92);
      color: #111827;
      outline:none;
    }
    textarea{ width:100%; min-height: 110px; resize: vertical; }

    .pill-toggle{
      display:flex;
      border:1px solid rgba(15,23,42,.12);
      border-radius: 999px;
      overflow:hidden;
      background: #ffffff;
    }
    .pill-toggle button{
      border:none;
      padding: 7px 10px;
      font-weight: 800;
      font-size: 12px;
      cursor:pointer;
      color:#111827;
      background: transparent;
    }
    .pill-toggle button.active{
      background: rgba(59,130,246,.18);
    }

    /* Task table */
    .table-wrap{
      width:100%;
      overflow:auto;
      border-radius: 14px;
      border: 1px solid rgba(15,23,42,.10);
      background: #ffffff;
    }
    .rum-planner-page table{
      width: 1700px;
      border-collapse: collapse;
      font-size: 12px;
      color: #111827;
    }
    thead th{
      position: sticky;
      top:0;
      background: #f8fafc;
      border-bottom: 1px solid rgba(15,23,42,.12);
      padding: 10px 10px;
      text-align:left;
      white-space:nowrap;
      z-index:2;
    }
    tbody td{
      border-bottom: 1px solid rgba(15,23,42,.08);
      padding: 8px 10px;
      vertical-align: middle;
      white-space:nowrap;
    }
    tbody tr:hover td{ background: rgba(15,23,42,.03); }

    .sticky-col{ position: sticky; left:0; z-index:1; background: #fff; }
    .sticky-col-2{ position: sticky; left:110px; z-index:1; background: #fff; }

    .col-wbs{ width:110px; }
    .col-act{ width: 260px; }
    .col-trade{ width: 120px; }
    .col-phase{ width: 150px; }
    .col-start{ width: 70px; }
    .col-dur{ width: 70px; }
    .col-pred{ width: 90px; }
    .col-rel{ width: 70px; }
    .col-cost{ width: 120px; }
    .col-ac{ width: 120px; }
    .col-pct{ width: 70px; }
    .col-ev{ width: 120px; }
    .col-cv{ width: 150px; }
    .col-payapp{ width: 120px; }
    .col-lean{ width: 110px; }
    .col-cstatus{ width: 130px; }
    .col-bim{ width: 220px; }
    .col-actions{ width: 170px; }

    .row-actions{display:flex;gap:6px;flex-wrap:wrap;align-items:center;justify-content:flex-start}
    .tag-lean-status{
      font-weight: 900;
      font-size: 11px;
      padding: 4px 8px;
      border-radius: 999px;
      border: 1px solid rgba(17,24,39,0.12);
      background: rgba(255,255,255,.92);
      color:#111827;
    }
    .lean-status-lean-done{ background: rgba(22,163,74,.16); border-color: rgba(22,163,74,.25); color:#064e3b; }
    .lean-status-lean-constraint{ background: rgba(220,38,38,.14); border-color: rgba(220,38,38,.22); color:#7f1d1d; }
    .lean-status-lean-ready{ background: rgba(245,158,11,.16); border-color: rgba(245,158,11,.25); color:#78350f; }
    .lean-status-lean-none{ background: rgba(37,99,235,.12); border-color: rgba(37,99,235,.20); color:#1e3a8a; }

    .cell-input{
      width:100%;
      max-width:100%;
      border-radius: 10px;
      border: 1px solid rgba(17,24,39,0.14);
      background: rgba(255,255,255,.92);
      color:#111827;
      padding: 7px 8px;
      font-size: 12px;
      font-weight: 700;
      outline:none;
    }

    .ev-val{ font-weight: 900; }
    .cv-neg{ color:#b91c1c; font-weight:900; }
    .cv-pos{ color:#15803d; font-weight:900; }

    /* Charts */
    .chart-card{
      background: #ffffff;
      border: 1px solid rgba(15,23,42,.10);
      border-radius: 16px;
      padding: 12px;
      overflow:hidden;
      color:#111827;
      min-height: 260px;
      display:flex;
      flex-direction:column;
      gap:8px;
    }
    .chart-card h3{
      margin:0 0 10px;
      font-family: "Baloo 2";
      font-size: 16px;
      letter-spacing:.2px;
    }
    .chart-grid{
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 12px;
    }
    .error-list{
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 10px;
      margin-bottom: 12px;
    }
    .error-item{
      padding: 10px 12px;
      border-radius: 12px;
      border: 1px solid rgba(220,38,38,.25);
      background: rgba(254,226,226,.6);
      color: #7f1d1d;
      font-size: 12px;
      font-weight: 700;
    }
    .gantt-controls{
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 12px;
      padding: 12px 14px;
      border-bottom: 1px solid rgba(15,23,42,.08);
      background: #ffffff;
    }
    .gantt-controls .control{
      display:flex;
      flex-direction:column;
      gap:6px;
      font-size:12px;
      color:#374151;
      font-weight:600;
    }
    .gantt-controls textarea{
      min-height: 70px;
    }
    .gantt-controls .control-inline{
      display:flex;
      flex-direction:column;
      gap:10px;
      align-items:flex-start;
      justify-content:flex-end;
    }
    .gantt-controls .control-inline label{
      font-size:12px;
      font-weight:600;
      color:#374151;
      display:flex;
      align-items:center;
      gap:8px;
    }
    .chart-card canvas{
      width:100% !important;
      height: 220px !important;
      max-height: 240px;
    }
    .chart-card canvas.chart-donut{
      height: 200px !important;
      max-height: 220px;
    }

    .badge-bim{
      display:inline-flex; align-items:center; gap:6px;
      font-size:11px;
      font-weight:900;
      padding: 6px 10px;
      border-radius: 999px;
      background: rgba(59,130,246,.12);
      border: 1px solid rgba(59,130,246,.20);
      color: #1e3a8a;
    }

    /* Gantt */
    .gantt-wrap{
      background: #ffffff;
      border: 1px solid rgba(15,23,42,.10);
      border-radius: 18px;
      overflow:hidden;
      box-shadow: var(--shadow);
    }
    .gantt-head{
      padding: 12px 14px;
      border-bottom: 1px solid rgba(15,23,42,.08);
      display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;
      background: #f8fafc;
      color:#111827;
    }
    .gantt-head .meta{
      font-size: 12px;
      color: #1f2937;
      font-weight: 700;
      display:flex; gap:10px; flex-wrap:wrap; align-items:center;
    }
    .gantt-grid{
      display:grid;
      grid-template-columns: 370px 1fr;
      min-height: 360px;
    }
    @media (max-width: 1180px){
      .gantt-grid{ grid-template-columns: 1fr; }
    }
    .gantt-left{
      border-right: 1px solid rgba(15,23,42,.08);
      padding: 10px;
      background: #fff;
    }
    .gantt-right{
      position:relative;
      overflow:auto;
      padding: 10px;
      background: #fff;
    }
    .gantt-timeline{
      display:grid;
      grid-auto-flow: column;
      grid-auto-columns: minmax(40px, 1fr);
      gap:0;
      position:sticky;
      top:0;
      z-index:4;
      background: #f8fafc;
      border-bottom: 1px solid rgba(15,23,42,.08);
    }
    .time-cell{
      text-align:center;
      font-weight: 900;
      font-size: 10px;
      padding: 6px 0;
      border-right: 1px solid rgba(15,23,42,.08);
      color: #111827;
      user-select:none;
      letter-spacing:.2px;
    }
    .track{
      display:grid;
      grid-auto-flow: column;
      grid-auto-columns: minmax(40px, 1fr);
      border-bottom: 1px solid rgba(15,23,42,.06);
      position:relative;
      height: 34px;
    }
    .track:hover{ background: rgba(15,23,42,.03); }
    .bar{
      position:absolute;
      height: 18px;
      top: 8px;
      border-radius: 999px;
      background: rgba(37,99,235,.75);
      border: 1px solid rgba(37,99,235,.45);
      box-shadow: 0 8px 18px rgba(15,23,42,.15);
      cursor: grab;
    }
    .bar:active{ cursor: grabbing; }
    .bar.baseline{
      background: rgba(59,130,246,.18);
      border: 1px dashed rgba(59,130,246,.45);
      box-shadow: none;
      height: 10px;
      top: 20px;
      opacity:.85;
    }
    .today-line{
      position:absolute;
      top:0;
      width:2px;
      background: rgba(220,38,38,.9);
      box-shadow: 0 0 14px rgba(220,38,38,.65);
      z-index:6;
    }

    /* Drawer */
    .drawer-backdrop{
      position:fixed; inset:0;
      background: rgba(0,0,0,.55);
      backdrop-filter: blur(8px);
      display:none;
      z-index:50;
      align-items:stretch;
      justify-content:flex-end;
    }
    .drawer{
      width: min(520px, 95vw);
      height: 100%;
      background: #ffffff;
      border-left: 1px solid rgba(15,23,42,.10);
      box-shadow: -14px 0 60px rgba(15,23,42,.18);
      padding: 16px;
      overflow:auto;
      color:#111827;
    }
    .drawer-header{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap:12px;
      padding-bottom: 12px;
      border-bottom: 1px solid rgba(15,23,42,.08);
      margin-bottom: 12px;
    }
    .drawer-title{
      font-family: "Baloo 2";
      font-size: 18px;
      margin:0;
    }
    .drawer-sub{
      font-size: 12px;
      color: #4b5563;
      margin-top: 4px;
    }
    .drawer-body{ margin-bottom: 12px; }
    .drawer-footer{
      display:flex;
      align-items:center;
      justify-content:flex-end;
      gap:10px;
      border-top: 1px solid rgba(15,23,42,.08);
      padding-top: 12px;
    }
    .drawer h3{
      margin: 0 0 8px;
      font-family:"Baloo 2";
      font-size: 18px;
    }
    .drawer .sub{
      margin: 0 0 14px;
      color: #4b5563;
      font-size: 12px;
      line-height:1.35;
    }
    .drawer .grid2{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }
    @media (max-width: 520px){
      .drawer .grid2{ grid-template-columns: 1fr; }
    }
    .field label{
      display:block;
      font-size: 11px;
      font-weight: 900;
      color: #1f2937;
      margin-bottom: 6px;
    }
    .field span{
      display:block;
      font-size: 11px;
      font-weight: 900;
      color: #1f2937;
      margin-bottom: 6px;
    }
    .field input, .field select, .field textarea{
      width:100%;
    }

    /* Toast */
    .toast{
      position:fixed;
      right: 18px;
      bottom: 18px;
      z-index: 100;
      padding: 12px 12px;
      border-radius: 14px;
      background: rgba(255,255,255,.92);
      border: 1px solid rgba(17,24,39,.14);
      color:#111827;
      box-shadow: 0 18px 40px rgba(0,0,0,.35);
      display:none;
      max-width: 420px;
      font-weight: 800;
      font-size: 12px;
    }
    .toast.bad{ border-color: rgba(220,38,38,.25); background: rgba(254,242,242,.96); }

    /* Small hints */
    .hint{
      background: rgba(15,23,42,.03);
      border: 1px solid rgba(15,23,42,.10);
      border-radius: 14px;
      padding: 10px 12px;
      color: #374151;
      font-size: 12px;
      line-height:1.35;
    }
  
/* ====== NAV + LAYOUT SAFETY (para no romper tu navbar global) ====== */
.rum-planner-page{
  position:relative;
  z-index:1;
  width:100%;
  min-height:100vh;
  isolation:isolate;
}
.rum-planner-page *{ box-sizing:border-box; }
.rum-planner-page a{ color:inherit; text-decoration:none; }
.rum-planner-page button, .rum-planner-page input, .rum-planner-page select, .rum-planner-page textarea{
  font-family: inherit;
}
.rum-planner-page table{ border-collapse: collapse; }
.rum-planner-page canvas{ display:block; }
.rum-planner-page .grid{
  display:grid;
  grid-template-columns: 1fr;
  gap: 14px;
  align-items:start;
}
.rum-planner-page .row{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  align-items:center;
  justify-content:space-between;
  margin:0;
}
.rum-planner-page .btn{ border-radius: 12px; }
.rum-planner-page .panel{ overflow:hidden; }
.rum-planner-page .gantt-grid{ display:grid; grid-template-columns: 1fr; }
.rum-planner-page .gantt-timeline{ grid-column: 1 / -1; }
.rum-planner-page .gantt-body{
  display:grid;
  grid-template-columns: 370px 1fr;
  min-width:0;
}
@media (max-width: 1180px){
  .rum-planner-page .gantt-body{ grid-template-columns: 1fr; }
  .rum-planner-page .gantt-left{
    border-right:none;
    border-bottom: 1px solid rgba(15,23,42,.10);
  }
}
.rum-planner-page .gantt-left,
.rum-planner-page .gantt-right{
  min-width:0;
}
.rum-planner-wrap{
  max-width: 1400px;
  margin: 26px auto 60px;
  padding: 0 18px;
}
@media (max-width: 900px){
  .rum-planner-wrap{ padding: 0 12px; }
}
/* Evita que algún CSS externo fuerce blancos raros */
.rum-planner-page .card{ background: #ffffff; border:1px solid rgba(15,23,42,.10); color:#111827; }
.rum-planner-page .muted{ color: #6b7280; }
.rum-planner-page h1,
.rum-planner-page h2,
.rum-planner-page h3,
.rum-planner-page h4,
.rum-planner-page h5{ color:#111827; }
.rum-planner-page table thead th{ color:#111827; }
.rum-planner-page table tbody td{ color:#111827; }
.rum-planner-page .panel-header p{ color: #4b5563; }
.rum-planner-page .gantt-head strong{ color:#111827; }
.rum-planner-page .table-wrap{ background:#ffffff; }
.rum-planner-page #heatWrap{ background:#ffffff; border-color: rgba(15,23,42,.10); }

  </style>
</head>
<body>
<?php
  // Navbar (usa tu navbar del sitio si existe)
  $nav = $_SERVER['DOCUMENT_ROOT'] . '/navbar.php';
  if (is_readable($nav)) {
    try {
      include_once $nav;
    } catch (Throwable $e) {
      error_log('Navbar include failed: ' . $e->getMessage());
    }
  }
?>

<div class="rum-planner-page">

  <div id="stars-host" class="stars-container"></div>
  <div class="satellite satellite-1"></div>
  <div class="satellite satellite-2"></div>

  <div class="rum-shell">
    <div class="nav-wrap">
      <div class="nav-fallback">
        <div class="brand">
          <div class="logo">R</div>
          <span class="ch">RÜM Tools</span>
        </div>
        <div class="right">
          <span class="chip">Planner 5D</span>
          <span class="chip">Hola, <?php echo htmlspecialchars($USER_NAME); ?></span>
        </div>
      </div>
    </div>

    <div class="page-head">
      <div>
        <h1>RÜM Planner 5D</h1>
        <p>
          Planner híbrido: <strong>MS Project + Last Planner + EV</strong>. El enlace BIM recomendado es <strong>RUM_WBS</strong>.
          Importa tu JSON del modelo para vincular objetos por WBS y exporta updates para Revit por <strong>UniqueId</strong>.
        </p>
      </div>
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;justify-content:flex-end">
        <span class="chip">WBS = llave</span>
        <span class="chip">UniqueId = apply back</span>
        <span class="chip">Lean + EV</span>
      </div>
    </div>

    <div class="grid">
      <div>

    <section class="panel" style="margin-top:12px">
      <div class="panel-header">
        <div>
          <h2>Actividades (editable) + Last Planner</h2>
          <p>Tabla compacta tipo MS Project. Setea baseline, marca % avance, importa/exporta JSON.</p>
        </div>
        <div class="actions">
          <div class="pill-toggle" id="leanToggle">
            <button type="button" data-mode="normal" class="active">Normal</button>
            <button type="button" data-mode="lean">Lean (restr.)</button>
          </div>
          <button class="btn btn-primary" id="btnAdd">+ Actividad</button>
          <button class="btn btn-warn" id="btnBaseline">Set Baseline</button>
          <button class="btn" id="btnSaveLocal">Guardar Local</button>
          <button class="btn" id="btnLoadLocal">Cargar Local</button>
          <button class="btn btn-mini" id="btnImportBim">Importar BIM JSON</button>
          <input id="fileBimJson" type="file" accept="application/json" style="display:none" />
          <button class="btn btn-mini" id="btnExportUpdates" title="Genera el JSON para que el add-in de Revit aplique los parámetros">Export Updates (Revit)</button>
          <button class="btn btn-danger" id="btnReset">Reset</button>
        </div>
      </div>

      <div class="filters">
        <select id="fPhase">
          <option value="">Todas las fases</option>
          <option>Preconstrucción</option><option>Cimentación</option><option>Estructura</option>
          <option>Envolvente</option><option>MEP</option><option>Acabados</option><option>Exteriores</option>
        </select>
        <select id="fTrade">
          <option value="">Todos los trades</option>
          <option>Arquitectura</option><option>Estructuras</option><option>Interiores</option>
          <option>Eléctrico</option><option>Plomería</option><option>HVAC</option><option>Landscape</option>
        </select>
        <input id="fText" type="text" placeholder="Buscar (WBS, actividad, pred...)" style="min-width:220px" />
        <span class="muted" id="resultCount"></span>
      </div>

      <div class="panel-body">
        <div class="table-wrap">
        <table id="taskTable">
          <thead>
            <tr>
              <th class="col-wbs sticky-col">WBS</th>
              <th class="col-act sticky-col-2">Actividad</th>
              <th class="col-trade">Trade</th>
              <th class="col-phase">Fase</th>
              <th class="col-start">Inicio (día)</th>
              <th class="col-dur">Dur. (días)</th>
              <th class="col-pred">Pred.</th>
              <th class="col-rel">Rel.</th>
              <th class="col-cost">Costo (BAC)</th>
              <th class="col-ac">Costo Real (AC)</th>
              <th class="col-pct">% Av.</th>
              <th class="col-ev">EV</th>
              <th class="col-cv">Var. Costo (CV)</th>
              <th class="col-payapp">Hito/Pago</th>
              <th class="col-lean">Lean Status</th>
              <th class="col-cstatus">Obra</th>
              <th class="col-bim">BIM UniqueId(s)</th>
              <th class="col-actions">Acciones</th>
            </tr>
          </thead>
          <tbody id="tbody"></tbody>
        </table>
      </div>

      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;align-items:center">
        <button class="btn btn-mini" id="btnExport">Export JSON</button>
        <button class="btn btn-mini" id="btnImport">Import JSON (desde textarea)</button>

        <!-- ✅ BOTÓN + INPUT PARA CARGAR ARCHIVO JSON -->
        <button class="btn btn-mini" id="btnLoadFile">Cargar JSON (archivo)</button>
        <input id="fileJson" type="file" accept="application/json" style="display:none" />

        <span class="muted">Tip: usa Export Updates (Revit) para mandar de regreso por UniqueId.</span>
      </div>

      <div style="margin-top:10px">
        <textarea id="ioText" placeholder="Import/Export JSON (planner y/o updates)"></textarea>
      </div>

      <div class="hint" style="margin-top:10px">
        <strong>Recomendación BIM:</strong> en Revit asigna <code>RUM_WBS</code> a elementos. Al importar el JSON del modelo, el planner vincula
        actividades por WBS y guarda <code>UniqueId</code> en cada actividad. Export Updates genera un JSON para que tu add-in aplique:
        <code>RUM_WBS</code>, <code>RUM_Start_Planned</code>, <code>RUM_End_Planned</code>, <code>RUM_ActivityDuration</code>,
        <code>RUM_ConstraintStatus</code> y <code>RUM_ConstructionStatus</code>.
      </div>
    
  <!-- Drawer lateral: Nueva Actividad -->
  <div id="activityDrawer" class="drawer-backdrop" aria-hidden="true">
    <aside class="drawer" role="dialog" aria-modal="true" aria-label="Nueva actividad">
      <div class="drawer-header">
        <div>
          <div class="drawer-title">Nueva actividad</div>
          <div class="drawer-sub">Rellena lo esencial. Lo demás lo puedes completar después.</div>
        </div>
        <button class="btn btn-ghost" id="btnCloseActivityDrawer" type="button">Cerrar</button>
      </div>

      <div class="drawer-body">
        <div class="grid2">
          <label class="field">
            <span>WBS</span>
            <input id="d_wbs" placeholder="A.01 / 03.02.001" />
          </label>
          <label class="field">
            <span>Trade</span>
            <select id="d_trade">
              <option value="Arquitectura">Arquitectura</option>
              <option value="Estructuras">Estructuras</option>
              <option value="MEP">MEP</option>
              <option value="Interiores">Interiores</option>
              <option value="Landscape">Landscape</option>
            </select>
          </label>
          <label class="field" style="grid-column:1/-1">
            <span>Actividad</span>
            <input id="d_name" placeholder="Nombre claro y accionable" />
          </label>

          <label class="field">
            <span>Fase</span>
            <select id="d_phase">
              <option value="Preconst">Preconstrucción</option>
              <option value="Cimentac">Cimentación</option>
              <option value="Estruct">Estructura</option>
              <option value="MEP">MEP</option>
              <option value="Acabados">Acabados</option>
              <option value="Cierre">Cierre</option>
            </select>
          </label>

          <label class="field">
            <span>Lean Status</span>
            <select id="d_lean">
              <option value="LEAN-NONE">LEAN-NONE</option>
              <option value="LEAN-READY">LEAN-READY</option>
              <option value="LEAN-CONSTRAINT">LEAN-CONSTRAINT</option>
              <option value="LEAN-LOOKAHEAD">LEAN-LOOKAHEAD</option>
            </select>
          </label>

          <label class="field">
            <span>Inicio (día)</span>
            <input id="d_start" type="number" min="1" value="1" />
          </label>
          <label class="field">
            <span>Duración (días)</span>
            <input id="d_dur" type="number" min="1" value="1" />
          </label>

          <label class="field">
            <span>Pred (WBS)</span>
            <input id="d_pred" placeholder="A.00" />
          </label>
          <label class="field">
            <span>Rel</span>
            <select id="d_rel">
              <option value="FS">FS</option>
              <option value="SS">SS</option>
              <option value="FF">FF</option>
              <option value="SF">SF</option>
            </select>
          </label>

          <label class="field">
            <span>Costo (BAC)</span>
            <input id="d_bac" type="number" min="0" step="100" value="0" />
          </label>
          <label class="field">
            <span>% Avance</span>
            <input id="d_pct" type="number" min="0" max="100" step="1" value="0" />
          </label>

          <label class="field" style="grid-column:1/-1">
            <span>BIM UniqueId(s)</span>
            <textarea id="d_uid" rows="2" placeholder="Pega uno o varios UniqueId separados por coma"></textarea>
            <div class="hint">El planner exporta updates por UniqueId para que tu add-in los aplique a Revit.</div>
          </label>
        </div>
      </div>

      <div class="drawer-footer">
        <button class="btn btn-ghost" id="btnCancelActivityDrawer" type="button">Cancelar</button>
        <button class="btn btn-primary" id="btnCreateActivity" type="button">Crear actividad</button>
      </div>
    </aside>
  </div>
</section>

    <section class="gantt-wrap" style="margin-top:12px">
      <div class="gantt-head">
        <div class="meta">
          <strong style="color:#111827">Gantt interactivo</strong>
          · Horizonte <span id="weeksLabel"></span> días
          · <span class="badge-bim">Hook BIM: Exporta JSON</span>
        </div>
        <div class="actions">
          <button class="btn btn-mini" id="btnZoomIn">Zoom +</button>
          <button class="btn btn-mini" id="btnZoomOut">Zoom -</button>
          <button class="btn btn-mini" id="btnFit">Fit</button>
          <button class="btn btn-mini" id="btnCellPlus">Día +</button>
          <button class="btn btn-mini" id="btnCellMinus">Día -</button>
        </div>
      </div>

      <div class="gantt-controls">
        <div class="control">
          <label for="projectStartDate">Fecha de inicio</label>
          <input type="date" id="projectStartDate" />
        </div>
        <div class="control">
          <label for="nonWorkingDays">Días no laborables (YYYY-MM-DD)</label>
          <textarea id="nonWorkingDays" placeholder="2025-01-01&#10;2025-02-05"></textarea>
        </div>
        <div class="control-inline">
          <label>
            <input type="checkbox" id="weekendsOff" checked />
            Sábado y domingo no laborables
          </label>
          <button class="btn btn-mini" id="btnApplyStartDate">Calcular fechas</button>
        </div>
      </div>

      <div class="gantt-grid">
        <div class="gantt-timeline" id="timeline"></div>

        <div class="gantt-body">
          <div class="gantt-left" id="ganttLeft"></div>
          <div class="gantt-right" id="ganttRight">
            <div class="today-line" id="todayLine" style="display:none"></div>
            <div id="ganttTracks"></div>
          </div>
        </div>
      </div>
    </section>

        <section class="panel" style="margin-top:12px">
          <div class="panel-header">
            <div>
              <h2>KPIs + Earned Value</h2>
              <p>KPIs base para PM. Minimal, sin saturar. Usa costo BAC por actividad, % y AC.</p>
            </div>
            <div class="actions">
              <span class="chip" id="cpiChip">CPI: —</span>
              <span class="chip" id="spiChip">SPI: —</span>
              <span class="chip" id="svChip">SV: —</span>
            </div>
          </div>

          <div class="panel-body">
            <div class="error-list">
              <div class="error-item" id="errorCost">Error 01: —</div>
              <div class="error-item" id="errorWbs">Error 02: —</div>
            </div>
            <div class="cards">
              <div class="card">
                <div class="k">BAC Total</div>
                <div class="v" id="kpiBAC">$0</div>
                <div class="s" id="kpiBAC2">Sumatoria costos</div>
              </div>
              <div class="card">
                <div class="k">EV Total</div>
                <div class="v" id="kpiEV">$0</div>
                <div class="s" id="kpiEV2">Ganado (% * BAC)</div>
              </div>
              <div class="card">
                <div class="k">AC Total</div>
                <div class="v" id="kpiAC">$0</div>
                <div class="s" id="kpiAC2">Costo real</div>
              </div>
            </div>

            <div class="chart-grid" style="margin-top:12px">
              <div class="chart-card">
                <h3>S-Curve (PV / EV / AC)</h3>
                <canvas id="chartSCurve" class="chart-canvas" height="180"></canvas>
              </div>

              <div class="chart-card">
                <h3>Distribución por trade</h3>
                <canvas id="chartDonut" class="chart-donut" height="140"></canvas>
              </div>

              <div class="chart-card">
                <h3>Costo por fase (BAC)</h3>
                <canvas id="chartPhaseCost" class="chart-canvas" height="180"></canvas>
              </div>

              <div class="chart-card">
                <h3>Estado de actividades</h3>
                <canvas id="chartStatus" class="chart-donut" height="160"></canvas>
              </div>

              <div class="chart-card">
                <h3>Cashflow diario (PV / AC)</h3>
                <canvas id="chartDaily" class="chart-canvas" height="180"></canvas>
              </div>

              <div class="chart-card">
                <h3>Heatmap Lean (restricciones por día)</h3>
                <div id="heatWrap" style="overflow:auto;border-radius:14px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.06)">
                  <canvas id="heatCanvas" width="900" height="220" style="display:block"></canvas>
                </div>
                <div class="muted" style="margin-top:8px" id="heatHint">Pasa el cursor por el heatmap para ver la celda.</div>
              </div>

              <div class="chart-card">
                <h3>Riesgo: issues (banderas)</h3>
                <canvas id="chartIssues" height="160"></canvas>
              </div>
            </div>
          </div>
        </section>

        <section class="panel" style="margin-top:12px">
          <div class="panel-header">
            <div>
              <h2>Procurement / Compras</h2>
              <p>Lista ligera de compras ligada a actividades. Puedes priorizar y marcar entregas.</p>
            </div>
            <div class="actions">
              <button class="btn btn-mini" id="btnAddPO">+ Compra</button>
            </div>
          </div>
          <div class="panel-body">
            <div class="table-wrap">
              <table style="width:100%">
                <thead>
                  <tr>
                    <th style="width:120px">WBS</th>
                    <th>Item</th>
                    <th style="width:120px">Proveedor</th>
                    <th style="width:110px">Estatus</th>
                    <th style="width:90px">ETA</th>
                    <th style="width:110px">Costo</th>
                    <th style="width:120px">Acciones</th>
                  </tr>
                </thead>
                <tbody id="poBody"></tbody>
              </table>
            </div>
          </div>
        </section>
      </div>
    </div>

  </div>

  <div class="drawer-backdrop" id="drawerBack">
    <div class="drawer">
      <div class="row" style="align-items:flex-start">
        <div>
          <h3 id="drawerTitle">Editar actividad</h3>
          <div class="sub" id="drawerSub">Edita parámetros clave. WBS enlaza BIM.</div>
        </div>
        <button class="btn btn-mini" id="btnCloseDrawer">Cerrar</button>
      </div>

      <div class="grid2">
        <div class="field">
          <label>WBS</label>
          <input id="dWBS" type="text" />
        </div>
        <div class="field">
          <label>Actividad</label>
          <input id="dName" type="text" />
        </div>

        <div class="field">
          <label>Trade</label>
          <select id="dTrade">
            <option>Arquitectura</option><option>Estructuras</option><option>Interiores</option>
            <option>Eléctrico</option><option>Plomería</option><option>HVAC</option><option>Landscape</option>
          </select>
        </div>
        <div class="field">
          <label>Fase</label>
          <select id="dPhase">
            <option>Preconstrucción</option><option>Cimentación</option><option>Estructura</option>
            <option>Envolvente</option><option>MEP</option><option>Acabados</option><option>Exteriores</option>
          </select>
        </div>

        <div class="field">
          <label>Inicio (día)</label>
          <input id="dStart" type="number" min="1" />
        </div>
        <div class="field">
          <label>Duración (días)</label>
          <input id="dDur" type="number" min="1" />
        </div>

        <div class="field">
          <label>Pred. (WBS)</label>
          <input id="dPred" type="text" />
        </div>
        <div class="field">
          <label>Relación</label>
          <select id="dRel">
            <option>FS</option><option>SS</option><option>FF</option><option>SF</option>
          </select>
        </div>

        <div class="field">
          <label>Costo (BAC)</label>
          <input id="dCost" type="number" min="0" />
        </div>
        <div class="field">
          <label>Costo Real (AC)</label>
          <input id="dAC" type="number" min="0" />
        </div>

        <div class="field">
          <label>% Avance</label>
          <input id="dPct" type="number" min="0" max="100" />
        </div>

        <div class="field">
          <label>Lean (restricción)</label>
          <select id="dRestr">
            <option value="0">Sin restricción</option>
            <option value="1">Con restricción</option>
          </select>
        </div>

        <div class="field">
          <label>Estatus (Last Planner)</label>
          <select id="dStatus">
            <option>Por liberar</option>
            <option>Listo</option>
            <option>En proceso</option>
            <option>Terminado</option>
          </select>
        </div>

        <div class="field">
          <label>Construction Status (RUM_ConstructionStatus)</label>
          <select id="dCStatus">
            <option value="">—</option>
            <option value="NOT_STARTED">No iniciado</option>
            <option value="IN_PROGRESS">En progreso</option>
            <option value="DONE">Terminado</option>
            <option value="ON_HOLD">Hold</option>
          </select>
        </div>

        <div class="field" style="grid-column:1/-1">
          <label>BIM UniqueId(s) (coma)</label>
          <input id="dUIDs" type="text" placeholder="Ej: 4b9c... , 8a11..." />
        </div>

        <div class="field" style="grid-column:1/-1">
          <label>Notas</label>
          <textarea id="dNotes" placeholder="Notas PM / restricciones / acuerdos"></textarea>
        </div>
      </div>

      <div class="row" style="margin-top:14px">
        <button class="btn btn-primary" id="btnSaveDrawer">Guardar cambios</button>
        <button class="btn btn-danger" id="btnDeleteDrawer">Eliminar actividad</button>
      </div>
    </div>
  </div>

  <div class="toast" id="toast"></div>

  <script>
    /* ===========================
       Estado base
       =========================== */

    const STORAGE_KEY = 'RUM_PLANNER_V1';
    const DAYS_PER_WEEK = 7;

    const BASE_CELL_WIDTH = 26;
    let HORIZON_DAYS = 70;
    let CELL_WIDTH = BASE_CELL_WIDTH;
    let ZOOM = 1.0;

    const state = {
      mode: 'normal',
      tasks: [
        { id:1, wbs:'A.01', name:'Trazo y replanteo', trade:'Arquitectura', phase:'Preconstrucción', start:1, dur:5, pred:null, rel:'FS', cost: 35000, ac:0, percent:0, status:'Por liberar', restr:0, revitId:'', revitUniqueIds:[], constructionStatus:'', notes:'', baselineStart:null, baselineDur:null, payapp:null },
        { id:2, wbs:'A.02', name:'Excavación', trade:'Estructuras', phase:'Cimentación', start:6, dur:10, pred:'A.01', rel:'FS', cost: 180000, ac:0, percent:0, status:'Por liberar', restr:1, revitId:'', revitUniqueIds:[], constructionStatus:'', notes:'', baselineStart:null, baselineDur:null, payapp:null },
        { id:3, wbs:'A.03', name:'Cimentación', trade:'Estructuras', phase:'Cimentación', start:16, dur:12, pred:'A.02', rel:'FS', cost: 420000, ac:0, percent:0, status:'Por liberar', restr:0, revitId:'', revitUniqueIds:[], constructionStatus:'', notes:'', baselineStart:null, baselineDur:null, payapp:null },
        { id:4, wbs:'A.04', name:'Estructura', trade:'Estructuras', phase:'Estructura', start:28, dur:15, pred:'A.03', rel:'FS', cost: 950000, ac:0, percent:0, status:'Por liberar', restr:0, revitId:'', revitUniqueIds:[], constructionStatus:'', notes:'', baselineStart:null, baselineDur:null, payapp:null },
        { id:5, wbs:'A.05', name:'MEP rough-in', trade:'HVAC', phase:'MEP', start:40, dur:10, pred:'A.04', rel:'SS', cost: 520000, ac:0, percent:0, status:'Por liberar', restr:0, revitId:'', revitUniqueIds:[], constructionStatus:'', notes:'', baselineStart:null, baselineDur:null, payapp:null },
        { id:6, wbs:'A.06', name:'Acabados base', trade:'Interiores', phase:'Acabados', start:50, dur:12, pred:'A.05', rel:'FS', cost: 680000, ac:0, percent:0, status:'Por liberar', restr:1, revitId:'', revitUniqueIds:[], constructionStatus:'', notes:'', baselineStart:null, baselineDur:null, payapp:null },
      ],
      milestones: [
        { id:1, name:'Hito 1: Cimentación OK', week:6, value: 250000 },
        { id:2, name:'Hito 2: Estructura OK', week:10, value: 600000 },
        { id:3, name:'Hito 3: Entrega', week:16, value: 900000 },
      ],
      procurement: [
        { id:1, wbs:'A.03', item:'Acero cimentación', vendor:'Proveedor A', status:'Pendiente', eta:'W4', cost: 120000 },
        { id:2, wbs:'A.04', item:'Cimbra / formaleta', vendor:'Proveedor B', status:'Ordenado', eta:'W7', cost: 80000 },
      ]
    };

    function money(v){
      const n = Number(v||0);
      return n.toLocaleString('es-MX',{style:'currency',currency:'MXN', maximumFractionDigits:0});
    }
    function escapeHtml(s){
      return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
    }
    function clamp(n,min,max){ return Math.max(min, Math.min(max,n)); }

    function formatDateISO(date){
      const y = date.getFullYear();
      const m = String(date.getMonth()+1).padStart(2,'0');
      const d = String(date.getDate()).padStart(2,'0');
      return `${y}-${m}-${d}`;
    }

    function mexicoHolidays(year){
      return [
        `${year}-01-01`,
        `${year}-02-05`,
        `${year}-03-21`,
        `${year}-05-01`,
        `${year}-09-16`,
        `${year}-11-18`,
        `${year}-12-25`,
      ];
    }

    function parseNonWorkingDates(){
      const raw = String(document.getElementById('nonWorkingDays')?.value || '');
      const parts = raw.split(/[\n,]+/).map(s=>s.trim()).filter(Boolean);
      return new Set(parts);
    }

    function isNonWorking(date, nonWorkingSet, weekendsOff){
      const day = date.getDay();
      const iso = formatDateISO(date);
      if (nonWorkingSet.has(iso)) return true;
      if (weekendsOff && (day === 0 || day === 6)) return true;
      return false;
    }

    function nextWorkingDate(date, nonWorkingSet, weekendsOff){
      const d = new Date(date);
      while (isNonWorking(d, nonWorkingSet, weekendsOff)){
        d.setDate(d.getDate() + 1);
      }
      return d;
    }

    function addWorkingDays(startDate, workDays, nonWorkingSet, weekendsOff){
      let current = new Date(startDate);
      let count = 0;
      while (count < workDays){
        if (!isNonWorking(current, nonWorkingSet, weekendsOff)){
          count += 1;
          if (count === workDays) break;
        }
        current.setDate(current.getDate() + 1);
      }
      return current;
    }

    function daysBetween(startDate, endDate){
      const ms = 24 * 60 * 60 * 1000;
      const start = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
      const end = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());
      return Math.round((end - start) / ms);
    }

    function normalizeTaskUnits(task, fromWeeks){
      if (!fromWeeks) return task;
      const start = Number(task.start||1) * DAYS_PER_WEEK;
      const dur = Math.max(1, Number(task.dur||1)) * DAYS_PER_WEEK;
      const baselineStart = task.baselineStart ? Number(task.baselineStart) * DAYS_PER_WEEK : task.baselineStart;
      const baselineDur = task.baselineDur ? Math.max(1, Number(task.baselineDur||1)) * DAYS_PER_WEEK : task.baselineDur;
      return { ...task, start, dur, baselineStart, baselineDur };
    }

    function applyLoadedData(obj){
      if (obj.mode) state.mode = obj.mode;
      let fromWeeks = false;
      if (Number.isFinite(obj.horizon_days)){
        HORIZON_DAYS = Number(obj.horizon_days);
      } else if (Number.isFinite(obj.horizonDays)){
        HORIZON_DAYS = Number(obj.horizonDays);
      } else if (Number.isFinite(obj.horizonWeeks)){
        HORIZON_DAYS = Number(obj.horizonWeeks) * DAYS_PER_WEEK;
        fromWeeks = true;
      }
      if (Array.isArray(obj.tasks)){
        state.tasks = obj.tasks.map(t => normalizeTaskUnits(t, fromWeeks));
      }
      if (Array.isArray(obj.milestones)){
        state.milestones = obj.milestones.map(m=>{
          if (fromWeeks && Number.isFinite(m.week)){
            return { ...m, day: Number(m.week) * DAYS_PER_WEEK };
          }
          return m;
        });
      }
      state.procurement = Array.isArray(obj.procurement) ? obj.procurement : state.procurement;
    }

    function scheduleFromStartDate(){
      const dateValue = document.getElementById('projectStartDate')?.value;
      if (!dateValue) return toast('Selecciona una fecha de inicio', true);
      const startDate = new Date(dateValue + 'T00:00:00');
      const nonWorkingSet = parseNonWorkingDates();
      const weekendsOff = document.getElementById('weekendsOff')?.checked ?? true;

      const taskByWbs = new Map();
      state.tasks.forEach(t=>taskByWbs.set(String(t.wbs||'').trim(), t));
      const endDates = new Map();

      const sorted = [...state.tasks].sort((a,b)=>Number(a.start||0) - Number(b.start||0));
      sorted.forEach((t, index)=>{
        let earliestDate = startDate;
        const predKey = String(t.pred||'').trim();
        if (predKey && taskByWbs.has(predKey)){
          const predTask = taskByWbs.get(predKey);
          const predEnd = endDates.get(predTask.id);
          if (predEnd) {
            earliestDate = new Date(predEnd);
            earliestDate.setDate(earliestDate.getDate() + 1);
          }
        } else if (index > 0){
          const prev = sorted[index - 1];
          const prevEnd = endDates.get(prev.id);
          if (prevEnd) {
            earliestDate = new Date(prevEnd);
            earliestDate.setDate(earliestDate.getDate() + 1);
          }
        }

        const workStart = nextWorkingDate(earliestDate, nonWorkingSet, weekendsOff);
        const workDays = Math.max(1, Number(t.dur||1));
        const workEnd = addWorkingDays(workStart, workDays, nonWorkingSet, weekendsOff);
        endDates.set(t.id, workEnd);

        const startOffset = daysBetween(startDate, workStart) + 1;
        const endOffset = daysBetween(startDate, workEnd) + 1;
        t.start = Math.max(1, startOffset);
        t.dur = Math.max(1, endOffset - startOffset + 1);
      });

      const maxEnd = Math.max(...Array.from(endDates.values()).map(d=>daysBetween(startDate, d) + 1), HORIZON_DAYS);
      HORIZON_DAYS = clamp(maxEnd + 3, 10, 180);
      refreshAll();
      toast('Fechas recalculadas con días no laborables');
    }

    function formatDateISO(date){
      const y = date.getFullYear();
      const m = String(date.getMonth()+1).padStart(2,'0');
      const d = String(date.getDate()).padStart(2,'0');
      return `${y}-${m}-${d}`;
    }

    function mexicoHolidays(year){
      return [
        `${year}-01-01`,
        `${year}-02-05`,
        `${year}-03-21`,
        `${year}-05-01`,
        `${year}-09-16`,
        `${year}-11-18`,
        `${year}-12-25`,
      ];
    }

    function parseNonWorkingDates(){
      const raw = String(document.getElementById('nonWorkingDays')?.value || '');
      const parts = raw.split(/[\n,]+/).map(s=>s.trim()).filter(Boolean);
      return new Set(parts);
    }

    function isNonWorking(date, nonWorkingSet, weekendsOff){
      const day = date.getDay();
      const iso = formatDateISO(date);
      if (nonWorkingSet.has(iso)) return true;
      if (weekendsOff && (day === 0 || day === 6)) return true;
      return false;
    }

    function nextWorkingDate(date, nonWorkingSet, weekendsOff){
      const d = new Date(date);
      while (isNonWorking(d, nonWorkingSet, weekendsOff)){
        d.setDate(d.getDate() + 1);
      }
      return d;
    }

    function addWorkingDays(startDate, workDays, nonWorkingSet, weekendsOff){
      let current = new Date(startDate);
      let count = 0;
      while (count < workDays){
        if (!isNonWorking(current, nonWorkingSet, weekendsOff)){
          count += 1;
          if (count === workDays) break;
        }
        current.setDate(current.getDate() + 1);
      }
      return current;
    }

    function daysBetween(startDate, endDate){
      const ms = 24 * 60 * 60 * 1000;
      const start = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
      const end = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());
      return Math.round((end - start) / ms);
    }

    function normalizeTaskUnits(task, fromWeeks){
      if (!fromWeeks) return task;
      const start = Number(task.start||1) * DAYS_PER_WEEK;
      const dur = Math.max(1, Number(task.dur||1)) * DAYS_PER_WEEK;
      const baselineStart = task.baselineStart ? Number(task.baselineStart) * DAYS_PER_WEEK : task.baselineStart;
      const baselineDur = task.baselineDur ? Math.max(1, Number(task.baselineDur||1)) * DAYS_PER_WEEK : task.baselineDur;
      return { ...task, start, dur, baselineStart, baselineDur };
    }

    function applyLoadedData(obj){
      if (obj.mode) state.mode = obj.mode;
      let fromWeeks = false;
      if (Number.isFinite(obj.horizon_days)){
        HORIZON_DAYS = Number(obj.horizon_days);
      } else if (Number.isFinite(obj.horizonDays)){
        HORIZON_DAYS = Number(obj.horizonDays);
      } else if (Number.isFinite(obj.horizonWeeks)){
        HORIZON_DAYS = Number(obj.horizonWeeks) * DAYS_PER_WEEK;
        fromWeeks = true;
      }
      if (Array.isArray(obj.tasks)){
        state.tasks = obj.tasks.map(t => normalizeTaskUnits(t, fromWeeks));
      }
      if (Array.isArray(obj.milestones)){
        state.milestones = obj.milestones.map(m=>{
          if (fromWeeks && Number.isFinite(m.week)){
            return { ...m, day: Number(m.week) * DAYS_PER_WEEK };
          }
          return m;
        });
      }
      state.procurement = Array.isArray(obj.procurement) ? obj.procurement : state.procurement;
    }

    function scheduleFromStartDate(){
      const dateValue = document.getElementById('projectStartDate')?.value;
      if (!dateValue) return toast('Selecciona una fecha de inicio', true);
      const startDate = new Date(dateValue + 'T00:00:00');
      const nonWorkingSet = parseNonWorkingDates();
      const weekendsOff = document.getElementById('weekendsOff')?.checked ?? true;

      const taskByWbs = new Map();
      state.tasks.forEach(t=>taskByWbs.set(String(t.wbs||'').trim(), t));
      const endDates = new Map();

      const sorted = [...state.tasks].sort((a,b)=>Number(a.start||0) - Number(b.start||0));
      sorted.forEach((t, index)=>{
        let earliestDate = startDate;
        const predKey = String(t.pred||'').trim();
        if (predKey && taskByWbs.has(predKey)){
          const predTask = taskByWbs.get(predKey);
          const predEnd = endDates.get(predTask.id);
          if (predEnd) {
            earliestDate = new Date(predEnd);
            earliestDate.setDate(earliestDate.getDate() + 1);
          }
        } else if (index > 0){
          const prev = sorted[index - 1];
          const prevEnd = endDates.get(prev.id);
          if (prevEnd) {
            earliestDate = new Date(prevEnd);
            earliestDate.setDate(earliestDate.getDate() + 1);
          }
        }

        const workStart = nextWorkingDate(earliestDate, nonWorkingSet, weekendsOff);
        const workDays = Math.max(1, Number(t.dur||1));
        const workEnd = addWorkingDays(workStart, workDays, nonWorkingSet, weekendsOff);
        endDates.set(t.id, workEnd);

        const startOffset = daysBetween(startDate, workStart) + 1;
        const endOffset = daysBetween(startDate, workEnd) + 1;
        t.start = Math.max(1, startOffset);
        t.dur = Math.max(1, endOffset - startOffset + 1);
      });

      const maxEnd = Math.max(...Array.from(endDates.values()).map(d=>daysBetween(startDate, d) + 1), HORIZON_DAYS);
      HORIZON_DAYS = clamp(maxEnd + 3, 10, 180);
      refreshAll();
      toast('Fechas recalculadas con días no laborables');
    }

    function normalizeTaskUnits(task, fromWeeks){
      if (!fromWeeks) return task;
      const start = Number(task.start||1) * DAYS_PER_WEEK;
      const dur = Math.max(1, Number(task.dur||1)) * DAYS_PER_WEEK;
      const baselineStart = task.baselineStart ? Number(task.baselineStart) * DAYS_PER_WEEK : task.baselineStart;
      const baselineDur = task.baselineDur ? Math.max(1, Number(task.baselineDur||1)) * DAYS_PER_WEEK : task.baselineDur;
      return { ...task, start, dur, baselineStart, baselineDur };
    }

    function applyLoadedData(obj){
      if (obj.mode) state.mode = obj.mode;
      let fromWeeks = false;
      if (Number.isFinite(obj.horizon_days)){
        HORIZON_DAYS = Number(obj.horizon_days);
      } else if (Number.isFinite(obj.horizonDays)){
        HORIZON_DAYS = Number(obj.horizonDays);
      } else if (Number.isFinite(obj.horizonWeeks)){
        HORIZON_DAYS = Number(obj.horizonWeeks) * DAYS_PER_WEEK;
        fromWeeks = true;
      }
      if (Array.isArray(obj.tasks)){
        state.tasks = obj.tasks.map(t => normalizeTaskUnits(t, fromWeeks));
      }
      if (Array.isArray(obj.milestones)){
        state.milestones = obj.milestones.map(m=>{
          if (fromWeeks && Number.isFinite(m.week)){
            return { ...m, day: Number(m.week) * DAYS_PER_WEEK };
          }
          return m;
        });
      }
      state.procurement = Array.isArray(obj.procurement) ? obj.procurement : state.procurement;
    }

    function toast(msg, bad=false){
      const el = document.getElementById('toast');
      el.textContent = msg;
      el.classList.toggle('bad', !!bad);
      el.style.display = 'block';
      clearTimeout(window.__toastT);
      window.__toastT = setTimeout(()=>{ el.style.display='none'; }, 2600);
    }

    function downloadText(text, filename){
      const blob = new Blob([text], {type:'application/json'});
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      a.remove();
      setTimeout(()=>URL.revokeObjectURL(a.href), 800);
    }

    /* ===========================
       Helpers de tasks
       =========================== */

    function getTask(id){ return state.tasks.find(x=>x.id===id) || null; }

    function nextTaskId(){ return (Math.max(0,...state.tasks.map(x=>x.id))+1); }

    function getLeanStatus(t) {
      if (t.status === 'Terminado') return 'lean-done';
      if (t.restr === 1) return 'lean-constraint';
      if (t.status === 'Listo') return 'lean-ready';
      return 'lean-none';
    }

    function calcEV(t){
      const bac = Number(t.cost||0);
      const pct = clamp(Number(t.percent||0),0,100)/100;
      return bac * pct;
    }
    /* ===========================
       Render table + filtros
       =========================== */

    function filteredTasks(){
      const ph = document.getElementById('fPhase').value.trim();
      const tr = document.getElementById('fTrade').value.trim();
      const tx = document.getElementById('fText').value.trim().toLowerCase();

      return state.tasks.filter(x=>{
        if (ph && String(x.phase||'')!==ph) return false;
        if (tr && String(x.trade||'')!==tr) return false;
        if (tx){
          const blob = `${x.wbs} ${x.name} ${x.pred||''}`.toLowerCase();
          if (!blob.includes(tx)) return false;
        }
        return true;
      });
    }

    function renderTable(){
      const list = filteredTasks();
      document.getElementById('resultCount').textContent = `${list.length} actividades`;

      let rows = '';
      list.forEach(x=>{
        const ev = calcEV(x);
        const ac = Number(x.ac||0);
        const cv = ev - ac;
        const cvClass = (cv<0) ? 'cv-neg' : 'cv-pos';
        const leanStatus = getLeanStatus(x);

        rows += `
          <tr data-id="${x.id}">
            <td class="col-wbs sticky-col">
              <input class="cell-input" data-k="wbs" value="${escapeHtml(x.wbs)}">
            </td>
            <td class="col-act sticky-col-2">
              <input class="cell-input" data-k="name" value="${escapeHtml(x.name)}">
            </td>
            <td class="col-trade"><input class="cell-input" data-k="trade" value="${escapeHtml(x.trade)}"></td>
            <td class="col-phase"><input class="cell-input" data-k="phase" value="${escapeHtml(x.phase)}"></td>

            <td class="col-start"><input class="cell-input" data-k="start" type="number" min="1" value="${Number(x.start||1)}"></td>
            <td class="col-dur"><input class="cell-input" data-k="dur" type="number" min="1" value="${Number(x.dur||1)}"></td>

            <td class="col-pred"><input class="cell-input" data-k="pred" value="${escapeHtml(x.pred||'')}"></td>
            <td class="col-rel"><input class="cell-input" data-k="rel" value="${escapeHtml(x.rel||'FS')}"></td>

            <td class="col-cost"><input class="cell-input" data-k="cost" type="number" min="0" value="${Number(x.cost||0)}"></td>
            <td class="col-ac"><input class="cell-input" data-k="ac" type="number" min="0" value="${Number(x.ac||0)}"></td>
            <td class="col-pct"><input class="cell-input" data-k="percent" type="number" min="0" max="100" value="${Number(x.percent||0)}"></td>

            <td class="col-ev ev-val">${money(ev)}</td>
            <td class="col-cv ${cvClass}">${money(cv)}</td>

            <td class="col-payapp"><input class="cell-input" data-k="payapp" value="${escapeHtml(x.payapp||'')}"></td>

            <td class="col-lean">
              <span class="tag-lean-status lean-status-${leanStatus}">${leanStatus.toUpperCase()}</span>
            </td>

            <td class="col-cstatus">
              <select class="cell-input" data-k="constructionStatus">
                <option value="">—</option>
                <option value="NOT_STARTED" ${x.constructionStatus==='NOT_STARTED'?'selected':''}>No iniciado</option>
                <option value="IN_PROGRESS" ${x.constructionStatus==='IN_PROGRESS'?'selected':''}>En progreso</option>
                <option value="DONE" ${x.constructionStatus==='DONE'?'selected':''}>Terminado</option>
                <option value="ON_HOLD" ${x.constructionStatus==='ON_HOLD'?'selected':''}>Hold</option>
              </select>
            </td>

            <td class="col-bim">
              <input class="cell-input" data-k="revitUniqueIds" value="${escapeHtml((Array.isArray(x.revitUniqueIds)? x.revitUniqueIds : (x.revitId? [String(x.revitId)] : [])).join(','))}" placeholder="UniqueId(s) separados por coma">
              <div class="muted" style="margin-top:4px;font-size:11px">
                ${(Array.isArray(x.revitUniqueIds)? x.revitUniqueIds.length : (x.revitId?1:0))? `${(Array.isArray(x.revitUniqueIds)? x.revitUniqueIds.length : 1)} elemento(s)` : 'Sin vínculo BIM'}
              </div>
            </td>

            <td class="col-actions">
              <div class="row-actions">
                <button class="btn btn-mini" data-act="open">Editar</button>
                <button class="btn btn-mini" data-act="dup">Duplicar</button>
                <button class="btn btn-mini" data-act="del" style="border-color:rgba(220,38,38,.35)">Borrar</button>
              </div>
            </td>
          </tr>
        `;
      });

      document.getElementById('tbody').innerHTML = rows || `<tr><td colspan="18" style="color:#6b7280;padding:14px">Sin resultados.</td></tr>`;

      document.querySelectorAll('#tbody tr').forEach(tr=>{
        const id = Number(tr.dataset.id);

        tr.querySelectorAll('.cell-input').forEach(inp=>{
          inp.addEventListener('change', ()=>{
            const key = inp.dataset.k;
            const t = getTask(id);
            if (!t) return;

            let v = inp.value;

            if (key==='start' || key==='dur' || key==='cost' || key==='percent' || key==='ac'){
              v = Number(v);
              if (!Number.isFinite(v)) v = 0;
            }

            if (key==='start') t.start = clamp(v,1,HORIZON_DAYS);
            else if (key==='dur') t.dur = clamp(v,1,HORIZON_DAYS);
            else if (key==='percent') t.percent = clamp(v,0,100);
            else if (key==='cost') t.cost = Math.max(0,v);
            else if (key==='ac') t.ac = Math.max(0,v);
            else if (key==='pred') t.pred = (String(v||'').trim() || null);
            else if (key==='rel') t.rel = (String(v||'FS').trim() || 'FS');
            else if (key==='payapp') t.payapp = (String(v||'').trim() || null);
            else if (key==='constructionStatus') t.constructionStatus = (String(v||'').trim() || '');
            else if (key==='revitUniqueIds') {
              const rawIds = String(v||'').trim();
              t.revitUniqueIds = rawIds ? rawIds.split(',').map(s=>s.trim()).filter(Boolean) : [];
              // compatibilidad hacia atrás
              t.revitId = t.revitUniqueIds[0] || '';
            }
            else t[key] = v;

            refreshAll();
          });
        });

        tr.querySelectorAll('button[data-act]').forEach(btn=>{
          btn.addEventListener('click', ()=>{
            const act = btn.dataset.act;
            if (act==='open') openDrawer(id);
            else if (act==='dup') duplicateTask(id);
            else if (act==='del') deleteTask(id);
          });
        });
      });
    }

    function duplicateTask(id){
      const t = getTask(id);
      if (!t) return;
      const nextId = nextTaskId();
      const clone = JSON.parse(JSON.stringify(t));
      clone.id = nextId;
      clone.wbs = `${t.wbs}.copy`;
      clone.name = `${t.name} (copia)`;
      state.tasks.push(clone);
      refreshAll();
      toast('Actividad duplicada');
    }

    function deleteTask(id){
      const idx = state.tasks.findIndex(x=>x.id===id);
      if (idx<0) return;
      state.tasks.splice(idx,1);
      refreshAll();
      toast('Actividad eliminada');
    }

    /* ===========================
       Drawer (edición completa)
       =========================== */

    let drawerId = null;

    function openDrawer(id){
      const t = getTask(id);
      if (!t) return;

      drawerId = id;
      document.getElementById('drawerTitle').textContent = `Editar: ${t.wbs}`;
      document.getElementById('drawerSub').textContent = `WBS enlaza BIM. UniqueId(s) se usan para aplicar updates en Revit.`;

      document.getElementById('dWBS').value = t.wbs || '';
      document.getElementById('dName').value = t.name || '';
      document.getElementById('dTrade').value = t.trade || 'Arquitectura';
      document.getElementById('dPhase').value = t.phase || 'Preconstrucción';
      document.getElementById('dStart').value = Number(t.start||1);
      document.getElementById('dDur').value = Number(t.dur||1);
      document.getElementById('dPred').value = t.pred || '';
      document.getElementById('dRel').value = t.rel || 'FS';
      document.getElementById('dCost').value = Number(t.cost||0);
      document.getElementById('dAC').value = Number(t.ac||0);
      document.getElementById('dPct').value = Number(t.percent||0);
      document.getElementById('dRestr').value = String(t.restr||0);
      document.getElementById('dStatus').value = t.status || 'Por liberar';
      document.getElementById('dNotes').value = t.notes || '';
      document.getElementById('dCStatus').value = t.constructionStatus || '';
      const uids = Array.isArray(t.revitUniqueIds) ? t.revitUniqueIds : (t.revitId ? [String(t.revitId)] : []);
      document.getElementById('dUIDs').value = uids.join(',');

      document.getElementById('drawerBack').style.display = 'flex';
    }

    function closeDrawer(){
      drawerId = null;
      document.getElementById('drawerBack').style.display = 'none';
    }

    document.getElementById('btnCloseDrawer')?.addEventListener('click', closeDrawer);
    document.getElementById('drawerBack')?.addEventListener('click', (e)=>{
      if (e.target.id==='drawerBack') closeDrawer();
    });

    document.getElementById('btnSaveDrawer')?.addEventListener('click', ()=>{
      if (!drawerId) return;
      const t = getTask(drawerId);
      if (!t) return;

      t.wbs = String(document.getElementById('dWBS').value||'').trim();
      t.name = String(document.getElementById('dName').value||'').trim();
      t.trade = document.getElementById('dTrade').value;
      t.phase = document.getElementById('dPhase').value;
      t.start = clamp(Number(document.getElementById('dStart').value||1),1,HORIZON_DAYS);
      t.dur = clamp(Number(document.getElementById('dDur').value||1),1,HORIZON_DAYS);
      t.pred = (String(document.getElementById('dPred').value||'').trim() || null);
      t.rel = document.getElementById('dRel').value;
      t.cost = Math.max(0, Number(document.getElementById('dCost').value||0));
      t.ac = Math.max(0, Number(document.getElementById('dAC').value||0));
      t.percent = clamp(Number(document.getElementById('dPct').value||0),0,100);
      t.restr = Number(document.getElementById('dRestr').value||0);
      t.status = document.getElementById('dStatus').value;
      t.notes = String(document.getElementById('dNotes').value||'');
      t.constructionStatus = String(document.getElementById('dCStatus').value||'').trim();

      const rawIds = String(document.getElementById('dUIDs').value||'').trim();
      t.revitUniqueIds = rawIds ? rawIds.split(',').map(s=>s.trim()).filter(Boolean) : [];
      t.revitId = t.revitUniqueIds[0] || '';

      refreshAll();
      toast('Cambios guardados');
      closeDrawer();
    });

    document.getElementById('btnDeleteDrawer')?.addEventListener('click', ()=>{
      if (!drawerId) return;
      deleteTask(drawerId);
      closeDrawer();
    });

    /* ===========================
       Gantt + timeline
       =========================== */

    function buildTimeline(){
      const tl = document.getElementById('timeline');
      tl.style.gridAutoColumns = `${CELL_WIDTH}px`;
      tl.innerHTML = '';
      for (let day=1; day<=HORIZON_DAYS; day++){
        const cell = document.createElement('div');
        cell.className = 'time-cell';
        cell.textContent = `D${day}`;
        tl.appendChild(cell);
      }
      document.getElementById('weeksLabel').textContent = HORIZON_DAYS;
    }

    function renderGantt(){
      const left = document.getElementById('ganttLeft');
      const tracks = document.getElementById('ganttTracks');
      left.innerHTML = '';
      tracks.innerHTML = '';

      const list = filteredTasks();
      list.forEach(t=>{
        const row = document.createElement('div');
        row.style.display='flex';
        row.style.flexDirection='column';
        row.style.gap='2px';
        row.style.padding='6px 6px';
        row.style.borderBottom='1px solid rgba(15,23,42,.08)';
        row.innerHTML = `<div style="font-weight:900">${escapeHtml(t.wbs)}</div><div style="opacity:.75;font-size:11px">${escapeHtml(t.name)}</div>`;
        left.appendChild(row);

        const track = document.createElement('div');
        track.className = 'track';
        track.style.gridAutoColumns = `${CELL_WIDTH}px`;
        track.dataset.id = t.id;

        // baseline bar
        if (t.baselineStart && t.baselineDur){
          const b = document.createElement('div');
          b.className = 'bar baseline';
          const x = (Number(t.baselineStart)-1) * CELL_WIDTH;
          const w = Number(t.baselineDur) * CELL_WIDTH;
          b.style.left = `${x}px`;
          b.style.width = `${w}px`;
          track.appendChild(b);
        }

        // main bar
        const bar = document.createElement('div');
        bar.className = 'bar';
        const x0 = (Number(t.start||1)-1) * CELL_WIDTH;
        const w0 = Number(t.dur||1) * CELL_WIDTH;
        bar.style.left = `${x0}px`;
        bar.style.width = `${w0}px`;
        bar.title = `${t.wbs} | ${t.name} (D${t.start} +${t.dur})`;
        track.appendChild(bar);

        // drag logic
        let dragging = false;
        let dragStartX = 0;
        let origStart = t.start;

        bar.addEventListener('mousedown', (e)=>{
          dragging = true;
          dragStartX = e.clientX;
          origStart = Number(t.start||1);
          bar.style.cursor='grabbing';
        });
        window.addEventListener('mousemove', (e)=>{
          if (!dragging) return;
          const dx = e.clientX - dragStartX;
          const deltaDays = Math.round(dx / CELL_WIDTH);
          t.start = clamp(origStart + deltaDays, 1, HORIZON_DAYS);
          renderTable();
          renderGantt();
          refreshKpisCharts();
        });
        window.addEventListener('mouseup', ()=>{
          if (!dragging) return;
          dragging = false;
          bar.style.cursor='grab';
        });

        tracks.appendChild(track);
      });

      // today line (optional: día actual basado en hoy -> 1)
      const todayDay = 1;
      const line = document.getElementById('todayLine');
      if (todayDay >=1 && todayDay <= HORIZON_DAYS){
        line.style.display='block';
        line.style.left = `${(todayDay-1)*CELL_WIDTH}px`;
        line.style.height = `${Math.max(360, list.length*34)}px`;
      }else{
        line.style.display='none';
      }
    }
    /* ===========================
       EV / KPIs / Charts
       =========================== */

    let chartSCurve = null;
    let chartDonut = null;
    let chartPhaseCost = null;
    let chartStatus = null;
    let chartDaily = null;
    let chartIssues = null;

    function seriesPV(){
      // PV simple: distribuir BAC linealmente por duración dentro del horizonte (días)
      const pv = Array.from({length:HORIZON_DAYS},()=>0);
      state.tasks.forEach(t=>{
        const bac = Number(t.cost||0);
        if (bac<=0) return;
        const dur = Math.max(1, Number(t.dur||1));
        const per = bac/dur;
        for (let i=0;i<dur;i++){
          const d = (Number(t.start||1)-1)+i;
          if (d>=0 && d<pv.length) pv[d]+=per;
        }
      });
      return pv;
    }
    function seriesEV(){
      const ev = Array.from({length:HORIZON_DAYS},()=>0);
      state.tasks.forEach(t=>{
        const bac = Number(t.cost||0);
        const dur = Math.max(1, Number(t.dur||1));
        const pct = clamp(Number(t.percent||0),0,100)/100;
        const earned = bac*pct;
        const per = earned/dur;
        for (let i=0;i<dur;i++){
          const d = (Number(t.start||1)-1)+i;
          if (d>=0 && d<ev.length) ev[d]+=per;
        }
      });
      return ev;
    }
    function seriesAC(){
      // AC se suma en el día de fin planificado (simplificado)
      const ac = Array.from({length:HORIZON_DAYS},()=>0);
      state.tasks.forEach(t=>{
        const v = Number(t.ac||0);
        if (v<=0) return;
        const endDay = clamp((Number(t.start||1)+Number(t.dur||1)-1),1,HORIZON_DAYS);
        ac[endDay-1]+=v;
      });
      return ac;
    }
    function cum(arr){
      const out = [];
      let s=0;
      for (const v of arr){ s += Number(v||0); out.push(s); }
      return out;
    }

    function refreshKpisCharts(){
      const bacTotal = state.tasks.reduce((a,t)=>a+Number(t.cost||0),0);
      const evTotal = state.tasks.reduce((a,t)=>a+calcEV(t),0);
      const acTotal = state.tasks.reduce((a,t)=>a+Number(t.ac||0),0);

      document.getElementById('kpiBAC').textContent = money(bacTotal);
      document.getElementById('kpiEV').textContent = money(evTotal);
      document.getElementById('kpiAC').textContent = money(acTotal);

      var missingCost = state.tasks.filter(t=>Number(t.cost||0) <= 0).length;
      var missingWbs = state.tasks.filter(t=>!String(t.wbs||'').trim()).length;
      document.getElementById('errorCost').textContent = `Error 01: ${missingCost} actividades sin BAC`;
      document.getElementById('errorWbs').textContent = `Error 02: ${missingWbs} actividades sin WBS`;

      const missingCost = state.tasks.filter(t=>Number(t.cost||0) <= 0).length;
      const missingWbs = state.tasks.filter(t=>!String(t.wbs||'').trim()).length;
      document.getElementById('errorCost').textContent = `Error 01: ${missingCost} actividades sin BAC`;
      document.getElementById('errorWbs').textContent = `Error 02: ${missingWbs} actividades sin WBS`;

      const missingCost = state.tasks.filter(t=>Number(t.cost||0) <= 0).length;
      const missingWbs = state.tasks.filter(t=>!String(t.wbs||'').trim()).length;
      document.getElementById('errorCost').textContent = `Error 01: ${missingCost} actividades sin BAC`;
      document.getElementById('errorWbs').textContent = `Error 02: ${missingWbs} actividades sin WBS`;

      const PV = seriesPV();
      const EV = seriesEV();
      const AC = seriesAC();

      const PVc = cum(PV);
      const EVc = cum(EV);
      const ACc = cum(AC);

      const lastIdx = Math.max(
        PVc.findLastIndex(v=>v>0),
        EVc.findLastIndex(v=>v>0),
        ACc.findLastIndex(v=>v>0),
        0
      );

      const PVtd = PVc[lastIdx] || 0;
      const EVtd = EVc[lastIdx] || 0;
      const ACtd = ACc[lastIdx] || 0;

      const CPI = ACtd>0 ? (EVtd/ACtd) : 0;
      const SPI = PVtd>0 ? (EVtd/PVtd) : 0;
      const SV = EVtd - PVtd;

      document.getElementById('cpiChip').textContent = `CPI: ${CPI? CPI.toFixed(2) : '—'}`;
      document.getElementById('spiChip').textContent = `SPI: ${SPI? SPI.toFixed(2) : '—'}`;
      document.getElementById('svChip').textContent = `SV: ${SV? money(SV) : '—'}`;

      // S-curve chart
      const labels = Array.from({length:HORIZON_DAYS},(_,i)=>`D${i+1}`);
      const dayTick = (value, index) => {
        const label = labels[index];
        const day = Number(label?.replace('D',''));
        if (!day) return '';
        if (day === 1 || day === HORIZON_DAYS || day % 7 === 1) return label;
        return '';
      };
      const ctx1 = document.getElementById('chartSCurve').getContext('2d');

      if (chartSCurve) chartSCurve.destroy();
      chartSCurve = new Chart(ctx1, {
        type: 'line',
        data: {
          labels,
          datasets: [
            { label:'PV', data: PVc, tension:0.25, pointRadius:0, borderColor:'rgba(37,99,235,.9)' },
            { label:'EV', data: EVc, tension:0.25, pointRadius:0, borderColor:'rgba(16,185,129,.9)' },
            { label:'AC', data: ACc, tension:0.25, pointRadius:0, borderColor:'rgba(249,115,22,.9)' },
          ]
        },
        options: {
          responsive:true,
          maintainAspectRatio:false,
          plugins:{ legend:{ labels:{ boxWidth:10, boxHeight:10 } } },
          scales:{
            x:{ ticks:{ maxRotation:0, autoSkip:false, callback: dayTick } },
            y:{ beginAtZero:true, suggestedMax: Math.max(...PVc, ...EVc, ...ACc) * 1.15 || 1 }
          }
        }
      });

      // Donut by trade
      const byTrade = {};
      state.tasks.forEach(t=>{
        const tr = t.trade || 'Sin trade';
        byTrade[tr] = (byTrade[tr]||0) + Number(t.cost||0);
      });
      const ctx2 = document.getElementById('chartDonut').getContext('2d');
      if (chartDonut) chartDonut.destroy();
      chartDonut = new Chart(ctx2, {
        type:'doughnut',
        data:{
          labels: Object.keys(byTrade),
          datasets:[{ data:Object.values(byTrade) }]
        },
        options:{
          responsive:true,
          maintainAspectRatio:true,
          aspectRatio: 1.6,
          cutout:'65%',
          plugins:{ legend:{ position:'bottom' } }
        }
      });

      // Cost by phase (bar)
      const byPhase = {};
      state.tasks.forEach(t=>{
        const ph = t.phase || 'Sin fase';
        byPhase[ph] = (byPhase[ph]||0) + Number(t.cost||0);
      });
      const phaseLabels = Object.keys(byPhase);
      const phaseData = Object.values(byPhase);
      const ctxPhase = document.getElementById('chartPhaseCost').getContext('2d');
      if (chartPhaseCost) chartPhaseCost.destroy();
      chartPhaseCost = new Chart(ctxPhase, {
        type:'bar',
        data:{ labels: phaseLabels, datasets:[{ label:'BAC', data: phaseData, backgroundColor:'rgba(37,99,235,.55)' }] },
        options:{
          responsive:true,
          maintainAspectRatio:false,
          plugins:{ legend:{ display:false } },
          scales:{
            x:{ ticks:{ autoSkip:false, maxRotation:45, minRotation:0 } },
            y:{ beginAtZero:true, suggestedMax: Math.max(...phaseData, 1) * 1.2 }
          }
        }
      });

      // Status distribution
      const byStatus = {};
      state.tasks.forEach(t=>{
        const status = t.status || 'Por liberar';
        byStatus[status] = (byStatus[status]||0) + 1;
      });
      const ctxStatus = document.getElementById('chartStatus').getContext('2d');
      if (chartStatus) chartStatus.destroy();
      chartStatus = new Chart(ctxStatus, {
        type:'doughnut',
        data:{
          labels: Object.keys(byStatus),
          datasets:[{ data:Object.values(byStatus) }]
        },
        options:{
          responsive:true,
          maintainAspectRatio:true,
          aspectRatio: 1.6,
          cutout:'65%',
          plugins:{ legend:{ position:'bottom' } }
        }
      });

      // Daily cashflow (PV vs AC)
      const ctxDaily = document.getElementById('chartDaily').getContext('2d');
      if (chartDaily) chartDaily.destroy();
      const pvMax = Math.max(...PV, 0);
      const acMax = Math.max(...AC, 0);
      chartDaily = new Chart(ctxDaily, {
        type:'line',
        data:{
          labels,
          datasets:[
            { label:'PV diario', data: PV, tension:0.25, pointRadius:0, borderColor:'rgba(37,99,235,.9)' },
            { label:'AC diario', data: AC, tension:0.25, pointRadius:0, borderColor:'rgba(220,38,38,.85)' }
          ]
        },
        options:{
          responsive:true,
          maintainAspectRatio:false,
          plugins:{ legend:{ labels:{ boxWidth:10, boxHeight:10 } } },
          scales:{
            x:{ ticks:{ autoSkip:false, callback: dayTick } },
            y:{ beginAtZero:true, suggestedMax: Math.max(pvMax, acMax) * 1.2 || 1 }
          }
        }
      });

      // Daily cashflow (PV vs AC)
      const ctxDaily = document.getElementById('chartDaily').getContext('2d');
      if (chartDaily) chartDaily.destroy();
      const pvMax = Math.max(...PV, 0);
      const acMax = Math.max(...AC, 0);
      chartDaily = new Chart(ctxDaily, {
        type:'line',
        data:{
          labels,
          datasets:[
            { label:'PV diario', data: PV, tension:0.25, pointRadius:0, borderColor:'rgba(37,99,235,.9)' },
            { label:'AC diario', data: AC, tension:0.25, pointRadius:0, borderColor:'rgba(220,38,38,.85)' }
          ]
        },
        options:{
          responsive:true,
          maintainAspectRatio:false,
          plugins:{ legend:{ labels:{ boxWidth:10, boxHeight:10 } } },
          scales:{
            x:{ ticks:{ autoSkip:false, callback: dayTick } },
            y:{ beginAtZero:true, suggestedMax: Math.max(pvMax, acMax) * 1.2 || 1 }
          }
        }
      });

      // Cost by phase (bar)
      const byPhase = {};
      state.tasks.forEach(t=>{
        const ph = t.phase || 'Sin fase';
        byPhase[ph] = (byPhase[ph]||0) + Number(t.cost||0);
      });
      const phaseLabels = Object.keys(byPhase);
      const phaseData = Object.values(byPhase);
      const ctxPhase = document.getElementById('chartPhaseCost').getContext('2d');
      if (chartPhaseCost) chartPhaseCost.destroy();
      chartPhaseCost = new Chart(ctxPhase, {
        type:'bar',
        data:{ labels: phaseLabels, datasets:[{ label:'BAC', data: phaseData, backgroundColor:'rgba(37,99,235,.55)' }] },
        options:{
          responsive:true,
          maintainAspectRatio:false,
          plugins:{ legend:{ display:false } },
          scales:{
            x:{ ticks:{ autoSkip:false, maxRotation:45, minRotation:0 } },
            y:{ beginAtZero:true, suggestedMax: Math.max(...phaseData, 1) * 1.2 }
          }
        }
      });

      // Status distribution
      const byStatus = {};
      state.tasks.forEach(t=>{
        const status = t.status || 'Por liberar';
        byStatus[status] = (byStatus[status]||0) + 1;
      });
      const ctxStatus = document.getElementById('chartStatus').getContext('2d');
      if (chartStatus) chartStatus.destroy();
      chartStatus = new Chart(ctxStatus, {
        type:'doughnut',
        data:{
          labels: Object.keys(byStatus),
          datasets:[{ data:Object.values(byStatus) }]
        },
        options:{
          responsive:true,
          maintainAspectRatio:false,
          cutout:'65%',
          plugins:{ legend:{ position:'bottom' } }
        }
      });

      // Daily cashflow (PV vs AC)
      const ctxDaily = document.getElementById('chartDaily').getContext('2d');
      if (chartDaily) chartDaily.destroy();
      const pvMax = Math.max(...PV, 0);
      const acMax = Math.max(...AC, 0);
      chartDaily = new Chart(ctxDaily, {
        type:'line',
        data:{
          labels,
          datasets:[
            { label:'PV diario', data: PV, tension:0.25, pointRadius:0, borderColor:'rgba(37,99,235,.9)' },
            { label:'AC diario', data: AC, tension:0.25, pointRadius:0, borderColor:'rgba(220,38,38,.85)' }
          ]
        },
        options:{
          responsive:true,
          maintainAspectRatio:false,
          plugins:{ legend:{ labels:{ boxWidth:10, boxHeight:10 } } },
          scales:{
            x:{ ticks:{ autoSkip:true, maxTicksLimit:12 } },
            y:{ beginAtZero:true, suggestedMax: Math.max(pvMax, acMax) * 1.2 || 1 }
          }
        }
      });

      // Issues chart (usa restr como proxy)
      const issues = { 'Con restricción':0, 'Sin restricción':0 };
      state.tasks.forEach(t=>{
        if (t.restr===1) issues['Con restricción']++;
        else issues['Sin restricción']++;
      });
      const ctx3 = document.getElementById('chartIssues').getContext('2d');
      if (chartIssues) chartIssues.destroy();
      chartIssues = new Chart(ctx3, {
        type:'bar',
        data:{ labels:Object.keys(issues), datasets:[{ label:'Actividades', data:Object.values(issues) }] },
        options:{
          responsive:true,
          maintainAspectRatio:false,
          plugins:{ legend:{ display:false } },
          scales:{ y:{ beginAtZero:true, precision:0, suggestedMax: Math.max(...Object.values(issues), 1) * 1.4 } }
        }
      });

      renderHeatmap();
    }

    /* ===========================
       Heatmap Lean con cursor
       =========================== */

    function renderHeatmap(){
      const canvas = document.getElementById('heatCanvas');
      const wrap = document.getElementById('heatWrap');
      const hint = document.getElementById('heatHint');
      if (!canvas) return;
      const ctx = canvas.getContext('2d');

      const rows = filteredTasks();
      const cols = HORIZON_DAYS;
      const cellW = 24;
      const cellH = 22;

      const w = Math.max(900, cols*cellW + 10);
      const h = Math.max(220, rows.length*cellH + 40);
      canvas.width = w;
      canvas.height = h;

      ctx.clearRect(0,0,w,h);

      // header
      ctx.font = '700 11px Inter';
      ctx.fillStyle = '#111827';
      ctx.fillText('WBS / Día', 10, 16);

      for (let c=1;c<=cols;c++){
        const x = 110 + (c-1)*cellW;
        ctx.fillText(`D${c}`, x+6, 16);
      }

      // grid cells
      for (let r=0;r<rows.length;r++){
        const t = rows[r];
        const y = 30 + r*cellH;

        ctx.fillStyle = '#1f2937';
        ctx.fillText(String(t.wbs||''), 10, y+15);

        for (let c=1;c<=cols;c++){
          const x = 110 + (c-1)*cellW;
          const inSpan = (c >= t.start) && (c <= (t.start+t.dur-1));
          const isConstraint = (t.restr===1);
          let alpha = 0.10;
          if (inSpan && isConstraint) alpha = 0.30;
          else if (inSpan && !isConstraint) alpha = 0.16;

          ctx.fillStyle = `rgba(37,99,235,${alpha})`;
          if (inSpan && isConstraint) ctx.fillStyle = `rgba(220,38,38,${alpha})`;
          ctx.fillRect(x, y, cellW-2, cellH-2);
        }
      }

      // hover cursor
      const hover = {r:-1,c:-1};
      function drawHover(){
        // redraw simple overlay
        ctx.clearRect(0,0,w,h);
        // re-render base
        ctx.font = '700 11px Inter';
        ctx.fillStyle = '#111827';
        ctx.fillText('WBS / Día', 10, 16);
        for (let c=1;c<=cols;c++){
          const x = 110 + (c-1)*cellW;
          ctx.fillText(`D${c}`, x+6, 16);
        }
        for (let r=0;r<rows.length;r++){
          const t = rows[r];
          const y = 30 + r*cellH;
          ctx.fillStyle = '#1f2937';
          ctx.fillText(String(t.wbs||''), 10, y+15);

          for (let c=1;c<=cols;c++){
            const x = 110 + (c-1)*cellW;
            const inSpan = (c >= t.start) && (c <= (t.start+t.dur-1));
            const isConstraint = (t.restr===1);
            let alpha = 0.10;
            if (inSpan && isConstraint) alpha = 0.30;
            else if (inSpan && !isConstraint) alpha = 0.16;

            ctx.fillStyle = `rgba(37,99,235,${alpha})`;
            if (inSpan && isConstraint) ctx.fillStyle = `rgba(220,38,38,${alpha})`;
            ctx.fillRect(x, y, cellW-2, cellH-2);
          }
        }

        if (hover.r>=0 && hover.c>=1){
          const x = 110 + (hover.c-1)*cellW;
          const y = 30 + hover.r*cellH;
          ctx.strokeStyle = 'rgba(37,99,235,.85)';
          ctx.lineWidth = 2;
          ctx.strokeRect(x+1, y+1, cellW-4, cellH-4);
        }
      }

      canvas.onmousemove = (e)=>{
        const rect = canvas.getBoundingClientRect();
        const mx = e.clientX - rect.left;
        const my = e.clientY - rect.top;

        const c = Math.floor((mx - 110) / cellW) + 1;
        const r = Math.floor((my - 30) / cellH);

        if (c>=1 && c<=cols && r>=0 && r<rows.length){
          hover.c = c; hover.r = r;
          const t = rows[r];
          const inSpan = (c >= t.start) && (c <= (t.start+t.dur-1));
          hint.textContent = `WBS: ${t.wbs} · Día: D${c} · ${(inSpan? 'En actividad':'Fuera')} · ${(t.restr===1? 'CON restricción':'sin restricción')}`;
        }else{
          hover.c = -1; hover.r = -1;
          hint.textContent = 'Pasa el cursor por el heatmap para ver la celda.';
        }
        drawHover();
      };
      canvas.onmouseleave = ()=>{
        hover.c = -1; hover.r = -1;
        hint.textContent = 'Pasa el cursor por el heatmap para ver la celda.';
        drawHover();
      };

      drawHover();
      // scroll hint
      if (wrap) wrap.scrollLeft = 0;
    }

    /* ===========================
       Procurement
       =========================== */

    function renderPO(){
      const body = document.getElementById('poBody');
      let html = '';
      state.procurement.forEach(p=>{
        html += `
          <tr data-id="${p.id}">
            <td><input class="cell-input" data-k="wbs" value="${escapeHtml(p.wbs)}"></td>
            <td><input class="cell-input" data-k="item" value="${escapeHtml(p.item)}"></td>
            <td><input class="cell-input" data-k="vendor" value="${escapeHtml(p.vendor)}"></td>
            <td><input class="cell-input" data-k="status" value="${escapeHtml(p.status)}"></td>
            <td><input class="cell-input" data-k="eta" value="${escapeHtml(p.eta)}"></td>
            <td><input class="cell-input" data-k="cost" type="number" min="0" value="${Number(p.cost||0)}"></td>
            <td>
              <button class="btn btn-mini" data-act="delpo" style="border-color:rgba(220,38,38,.35)">Borrar</button>
            </td>
          </tr>
        `;
      });
      body.innerHTML = html || `<tr><td colspan="7" style="padding:14px;color:#6b7280">Sin compras.</td></tr>`;

      body.querySelectorAll('tr').forEach(tr=>{
        const id = Number(tr.dataset.id);
        tr.querySelectorAll('.cell-input').forEach(inp=>{
          inp.addEventListener('change', ()=>{
            const key = inp.dataset.k;
            const p = state.procurement.find(x=>x.id===id);
            if (!p) return;
            let v = inp.value;
            if (key==='cost') v = Math.max(0, Number(v||0));
            p[key] = v;
            toast('Compra actualizada');
          });
        });
        tr.querySelector('button[data-act="delpo"]')?.addEventListener('click', ()=>{
          const ix = state.procurement.findIndex(x=>x.id===id);
          if (ix>=0) state.procurement.splice(ix,1);
          renderPO();
          toast('Compra eliminada');
        });
      });
    }

    document.getElementById('btnAddPO')?.addEventListener('click', ()=>{
      const nextId = (Math.max(0,...state.procurement.map(x=>x.id))+1);
      state.procurement.push({ id:nextId, wbs:'', item:'Nuevo item', vendor:'', status:'Pendiente', eta:'', cost:0 });
      renderPO();
      toast('Compra agregada');
    });

    /* ===========================
       Botones principales
       =========================== */

    const startDateInput = document.getElementById('projectStartDate');
    if (startDateInput && !startDateInput.value){
      startDateInput.value = formatDateISO(new Date());
    }
    const nonWorkingInput = document.getElementById('nonWorkingDays');
    if (nonWorkingInput && !nonWorkingInput.value){
      const year = new Date().getFullYear();
      nonWorkingInput.value = mexicoHolidays(year).join('\n');
    }
    document.getElementById('btnApplyStartDate')?.addEventListener('click', scheduleFromStartDate);

    document.getElementById('leanToggle')?.addEventListener('click', (e)=>{
      const btn = e.target.closest('button[data-mode]');
      if (!btn) return;
      state.mode = btn.dataset.mode;
      document.querySelectorAll('#leanToggle button').forEach(x=>{
        x.classList.toggle('active', x.dataset.mode===state.mode);
      });
      refreshAll();
    });

    const activityDrawer = document.getElementById('activityDrawer');
    const resetActivityDrawer = () => {
      document.getElementById('d_wbs').value = '';
      document.getElementById('d_trade').value = 'Arquitectura';
      document.getElementById('d_name').value = '';
      document.getElementById('d_phase').value = 'Preconst';
      document.getElementById('d_lean').value = 'LEAN-NONE';
      document.getElementById('d_start').value = 1;
      document.getElementById('d_dur').value = 1;
      document.getElementById('d_pred').value = '';
      document.getElementById('d_rel').value = 'FS';
      document.getElementById('d_bac').value = 0;
      document.getElementById('d_pct').value = 0;
      document.getElementById('d_uid').value = '';
    };
    const openActivityDrawer = () => {
      if (!activityDrawer) return;
      activityDrawer.style.display = 'flex';
      activityDrawer.setAttribute('aria-hidden', 'false');
    };
    const closeActivityDrawer = () => {
      if (!activityDrawer) return;
      activityDrawer.style.display = 'none';
      activityDrawer.setAttribute('aria-hidden', 'true');
    };

    document.getElementById('btnAdd')?.addEventListener('click', ()=>{
      resetActivityDrawer();
      openActivityDrawer();
    });

    document.getElementById('btnCloseActivityDrawer')?.addEventListener('click', closeActivityDrawer);
    document.getElementById('btnCancelActivityDrawer')?.addEventListener('click', closeActivityDrawer);
    activityDrawer?.addEventListener('click', (e)=>{
      if (e.target === activityDrawer) closeActivityDrawer();
    });

    document.getElementById('btnCreateActivity')?.addEventListener('click', ()=>{
      const nextId = nextTaskId();
      const wbs = document.getElementById('d_wbs').value.trim() || `X.${nextId}`;
      const name = document.getElementById('d_name').value.trim() || 'Nueva actividad';
      const trade = document.getElementById('d_trade').value.trim() || 'Arquitectura';
      const phaseSelect = document.getElementById('d_phase');
      const phase = phaseSelect?.selectedOptions?.[0]?.textContent?.trim() || 'Preconstrucción';
      const lean = document.getElementById('d_lean').value;
      const start = clamp(Number(document.getElementById('d_start').value || 1), 1, HORIZON_DAYS);
      const dur = clamp(Number(document.getElementById('d_dur').value || 1), 1, HORIZON_DAYS);
      const predValue = document.getElementById('d_pred').value.trim();
      const rel = document.getElementById('d_rel').value.trim() || 'FS';
      const cost = Math.max(0, Number(document.getElementById('d_bac').value || 0));
      const percent = clamp(Number(document.getElementById('d_pct').value || 0), 0, 100);
      const uidRaw = document.getElementById('d_uid').value.trim();
      const uniqueIds = uidRaw ? uidRaw.split(',').map(s=>s.trim()).filter(Boolean) : [];

      state.tasks.push({
        id: nextId,
        wbs,
        name,
        trade,
        phase,
        start,
        dur,
        pred: predValue || null,
        rel,
        cost,
        percent,
        ac:0,
        status: lean === 'LEAN-READY' ? 'Listo' : 'Por liberar',
        restr: lean === 'LEAN-CONSTRAINT' ? 1 : 0,
        revitId: uniqueIds[0] || '',
        revitUniqueIds: uniqueIds,
        constructionStatus:'',
        notes:'',
        baselineStart:null,
        baselineDur:null,
        payapp:null
      });
      refreshAll();
      closeActivityDrawer();
      toast('Actividad creada');
    });

    document.getElementById('btnBaseline')?.addEventListener('click', ()=>{
      state.tasks.forEach(t=>{
        t.baselineStart = t.start;
        t.baselineDur = t.dur;
      });
      refreshAll();
      toast('Baseline guardada');
    });

    document.getElementById('btnSaveLocal')?.addEventListener('click', ()=>{
      const payload = {
        horizonDays:HORIZON_DAYS,
        horizonWeeks: Math.ceil(HORIZON_DAYS / DAYS_PER_WEEK),
        mode:state.mode,
        tasks:state.tasks,
        milestones:state.milestones,
        procurement:state.procurement
      };
      localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
      toast('Guardado en LocalStorage');
    });

    document.getElementById('btnLoadLocal')?.addEventListener('click', ()=>{
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return toast('No hay guardado local', true);
      try{
        const obj = JSON.parse(raw);
        state.mode = obj.mode || 'normal';
        applyLoadedData(obj);

        document.querySelectorAll('#leanToggle button').forEach(x=>{
          x.classList.toggle('active', x.dataset.mode===state.mode);
        });

        refreshAll();
        toast('Cargado desde LocalStorage');
      }catch(e){
        toast('Error al cargar local', true);
      }
    });

    // ==========================
    // BIM: Import modelo (JSON) y vincular por WBS / UniqueId
    // ==========================
    const fileBim = document.getElementById('fileBimJson');
    const btnImportBim = document.getElementById('btnImportBim');

    function getAny(obj, keys){
      for (const k of keys){
        if (obj && Object.prototype.hasOwnProperty.call(obj,k) && obj[k]!==undefined && obj[k]!==null) return obj[k];
      }
      return null;
    }

    function getParamFromElement(el, key){
      // Soporta múltiples esquemas de exportación:
      // - { params: {RUM_WBS:"..." } }
      // - { parameters: {"RUM_WBS":"..."} }
      // - { Parameters: [{Key:"RUM_WBS", Value:"..."}] }
      const p1 = getAny(el, ['params','parameters','Params','Parameters']);
      if (!p1) return null;

      if (Array.isArray(p1)){
        const hit = p1.find(x => String(getAny(x,['Key','key','Name','name'])).trim() === key);
        if (!hit) return null;
        return getAny(hit,['Value','value','Val','val']);
      }

      if (typeof p1 === 'object'){
        return getAny(p1,[key, key.toLowerCase(), key.toUpperCase()]);
      }

      return null;
    }

    function normalizeElements(modelJson){
      // Devuelve array de elementos de modelo
      if (!modelJson) return [];
      if (Array.isArray(modelJson)) return modelJson;
      const els = getAny(modelJson, ['elements','Elements','objs','objects','Objects','data']);
      if (Array.isArray(els)) return els;
      // algunos exports: {categories:{Walls:[...], Floors:[...]}}
      const cats = getAny(modelJson, ['categories','Categories']);
      if (cats && typeof cats==='object'){
        const out = [];
        Object.values(cats).forEach(arr=>{ if (Array.isArray(arr)) out.push(...arr); });
        return out;
      }
      return [];
    }

    function mergeBimIntoTasks(elements){
      let linked = 0;
      let created = 0;

      // Index tareas por WBS (texto)
      const idx = new Map();
      state.tasks.forEach(t=>{
        const w = String(t.wbs||'').trim();
        if (w) idx.set(w, t);
      });

      const byWbs = new Map();

      elements.forEach(el=>{
        const wbs = String(getParamFromElement(el,'RUM_WBS') || getAny(el,['RUM_WBS','wbs','WBS']) || '').trim();
        if (!wbs) return;

        const uid = String(getAny(el,['UniqueId','uniqueId','unique_id','RevitUniqueId','revitUniqueId']) || '').trim();
        if (!uid) return;

        if (!byWbs.has(wbs)) byWbs.set(wbs, {uniqueIds:new Set(), sample:el, count:0});
        const bag = byWbs.get(wbs);
        bag.uniqueIds.add(uid);
        bag.count++;
      });

      byWbs.forEach((bag, wbs)=>{
        let t = idx.get(wbs);
        if (!t){
          t = {
            id: nextTaskId(),
            wbs: wbs,
            name: `Actividad ${wbs}`,
            trade: '',
            phase: '',
            start: 1,
            dur: 1,
            pred: null,
            rel: 'FS',
            cost: 0,
            ac: 0,
            percent: 0,
            status: 'Por liberar',
            restr: 0,
            notes: '',
            baselineStart: null,
            baselineDur: null,
            payapp: null,
            constructionStatus: '',
            revitId: '',
            revitUniqueIds: []
          };
          state.tasks.push(t);
          idx.set(wbs,t);
          created++;
        }

        t.revitUniqueIds = Array.from(bag.uniqueIds);
        t.revitId = t.revitUniqueIds[0] || t.revitId || '';
        linked++;
      });

      refreshAll();
      toast(`BIM importado: ${linked} WBS vinculados (${created} creados).`);
    }

    btnImportBim?.addEventListener('click', ()=>{
      if (!fileBim) return;
      fileBim.value = '';
      fileBim.click();
    });

    fileBim?.addEventListener('change', async (ev)=>{
      const file = ev.target.files?.[0];
      if (!file) return;
      try{
        const raw = await file.text();
        const json = JSON.parse(raw);
        const els = normalizeElements(json);
        if (!els.length) return toast('No encontré elementos en el JSON (estructura no reconocida).', true);
        mergeBimIntoTasks(els);
      }catch(e){
        console.error(e);
        toast('Error al leer BIM JSON', true);
      }
    });

    // ==========================
    // Export Updates (para que Revit aplique parámetros por UniqueId)
    // ==========================
    document.getElementById('btnExportUpdates')?.addEventListener('click', ()=>{
      const out = {
        schema: 'RUM_PLANNER_UPDATES_V1',
        generated_at: new Date().toISOString(),
        horizon_days: HORIZON_DAYS,
        horizon_weeks: Math.ceil(HORIZON_DAYS / DAYS_PER_WEEK),
        tasks: []
      };

      state.tasks.forEach(t=>{
        const wbs = String(t.wbs||'').trim();
        const uids = Array.isArray(t.revitUniqueIds) ? t.revitUniqueIds : (t.revitId ? [String(t.revitId)] : []);
        if (!wbs || !uids.length) return;

        out.tasks.push({
          wbs,
          unique_ids: uids,
          params: {
            RUM_WBS: wbs,
            RUM_SequenceID: Number(t.seq||0) || null,
            RUM_WorkPackage: String(t.workpkg||'').trim() || null,
            RUM_ConstructionPhase: String(t.phase||'').trim() || null,
            RUM_LastPlannerZone: String(t.zone||'').trim() || null,
            RUM_ConstraintStatus: (t.restr===1 ? 'HOLD' : (String(t.status||'').toLowerCase()==='listo' ? 'READY' : 'NONE')),
            RUM_ActivityDuration: Number(t.dur||0) || null,
            RUM_Start_Planned: Number(t.start||0) ? Number(t.start) : null,
            RUM_End_Planned: (Number(t.start||0) && Number(t.dur||0)) ? (Number(t.start)+Number(t.dur)-1) : null,
            RUM_ConstructionStatus: String(t.constructionStatus||'').trim() || null
          }
        });
      });

      const pretty = JSON.stringify(out, null, 2);
      const ta = document.getElementById('ioText');
      if (ta){
        ta.value = pretty;
        ta.scrollIntoView({behavior:'smooth', block:'center'});
      }
      downloadText(pretty, `RUM_Planner_Updates_${Date.now()}.json`);
      toast(`Updates exportados: ${out.tasks.length} actividades.`);
    });

    document.getElementById('btnReset')?.addEventListener('click', ()=> location.reload());

    document.getElementById('btnExport')?.addEventListener('click', ()=>{
      const payload = {
        horizonDays: HORIZON_DAYS,
        horizonWeeks: Math.ceil(HORIZON_DAYS / DAYS_PER_WEEK),
        mode: state.mode,
        tasks: state.tasks,
        milestones: state.milestones,
        procurement: state.procurement
      };
      const pretty = JSON.stringify(payload, null, 2);
      document.getElementById('ioText').value = pretty;
      downloadText(pretty, `RUM_Planner_${Date.now()}.json`);
      toast('JSON exportado');
    });

    document.getElementById('btnImport')?.addEventListener('click', ()=>{
      const raw = document.getElementById('ioText').value.trim();
      if (!raw) return toast('Textarea vacío', true);
      try{
        const obj = JSON.parse(raw);
        if (obj && obj.schema === 'RUM_PLANNER_UPDATES_V1'){
          toast('Este JSON es de Updates (Revit). Importa un JSON de Planner para cargar datos.', true);
          return;
        }
        applyLoadedData(obj);
        refreshAll();
        toast('JSON importado');
      }catch(e){
        toast('Error JSON', true);
      }
    });

    // ✅ Cargar JSON desde archivo
    document.getElementById('btnLoadFile')?.addEventListener('click', ()=>{
      const f = document.getElementById('fileJson');
      f.value = '';
      f.click();
    });

    document.getElementById('fileJson')?.addEventListener('change', async (ev)=>{
      const file = ev.target.files?.[0];
      if (!file) return;
      try{
        const raw = await file.text();
        const obj = JSON.parse(raw);
        applyLoadedData(obj);
        refreshAll();
        toast('JSON cargado desde archivo');
      }catch(e){
        toast('Error al cargar archivo JSON', true);
      }
    });

    /* ===========================
       Zoom / Horizon controls
       =========================== */

    document.getElementById('btnCellPlus')?.addEventListener('click', ()=>{
      HORIZON_DAYS = clamp(HORIZON_DAYS+1, 10, 180);
      refreshAll();
    });
    document.getElementById('btnCellMinus')?.addEventListener('click', ()=>{
      HORIZON_DAYS = clamp(HORIZON_DAYS-1, 10, 180);
      refreshAll();
    });
    document.getElementById('btnZoomIn')?.addEventListener('click', ()=>{
      ZOOM = clamp(ZOOM + 0.1, 0.7, 1.6);
      CELL_WIDTH = Math.round(BASE_CELL_WIDTH * ZOOM);
      refreshAll();
    });
    document.getElementById('btnZoomOut')?.addEventListener('click', ()=>{
      ZOOM = clamp(ZOOM - 0.1, 0.7, 1.6);
      CELL_WIDTH = Math.round(BASE_CELL_WIDTH * ZOOM);
      refreshAll();
    });
    document.getElementById('btnFit')?.addEventListener('click', ()=>{
      ZOOM = 1.0;
      CELL_WIDTH = BASE_CELL_WIDTH;
      refreshAll();
    });

    /* ===========================
       Refresh master
       =========================== */

    function refreshAll(){
      buildTimeline();
      renderTable();
      renderGantt();
      refreshKpisCharts();
      renderPO();
    }

    ['fPhase','fTrade','fText'].forEach(id=>{
      document.getElementById(id)?.addEventListener('input', refreshAll);
      document.getElementById(id)?.addEventListener('change', refreshAll);
    });

    // init
    refreshAll();

  </script>
  </div>
</div>
</body>
</html>
