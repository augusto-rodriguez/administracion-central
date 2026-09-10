{{-- resources/views/partials/_modal_instructivos.blade.php --}}

<style>
    .btn-purple{background-color:#6f42c1!important;border-color:#6f42c1!important;color:#fff!important}
    .btn-purple:hover{background-color:#5a32a3!important;border-color:#5a32a3!important;color:#fff!important}
    .inst-header-bg{background:linear-gradient(135deg,#1a1a4e 0%,#2d2d7c 50%,#4a3f8f 100%)}
    #instructivoTabs .nav-link{color:rgba(255,255,255,.65)!important;font-weight:600;font-size:.85rem;border:none!important;padding:.5rem 1rem;border-radius:.375rem .375rem 0 0}
    #instructivoTabs .nav-link.active{color:#fff!important;background:rgba(255,255,255,.15)!important}
    #instructivoTabs .nav-link:hover{color:#fff!important}
    .paso-card-item{border:1px solid #dee2e6;background:#fff;text-align:center;cursor:pointer;transition:background .15s,box-shadow .15s;position:relative;min-height:130px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;padding:.75rem .5rem}
    .paso-card-item:hover{background:#f3ecff}
    .paso-card-item.sel{background:#f3ecff;box-shadow:inset 0 -3px 0 #6f42c1}
    .paso-card-item .p-num{padding:3px 10px;border-radius:2rem;background:#6f42c1;color:#fff;font-weight:800;font-size:.65rem;letter-spacing:.3px;display:inline-flex;align-items:center;justify-content:center;white-space:nowrap}
    .paso-card-item .p-ico{font-size:1.6rem;line-height:1}
    .paso-card-item .p-lbl{font-size:.72rem;font-weight:600;color:#333;line-height:1.25}
    .paso-card-item .p-badge{position:absolute;top:5px;right:5px;font-size:.55rem;font-weight:700;padding:2px 7px;border-radius:2rem;color:#fff}
    .paso-card-item .p-chevron{color:#6f42c1;font-size:.7rem;opacity:.3;transition:opacity .15s,transform .15s}
    .paso-card-item:hover .p-chevron{opacity:.7}
    .paso-card-item.sel .p-chevron{opacity:1;transform:scale(1.2)}
    .hint-bar{background:#f3ecff;text-align:center;padding:6px 1rem;font-size:.75rem;font-weight:600;color:#6f42c1;border-top:1px solid #e0cffc;border-bottom:1px solid #e0cffc;animation:hintPulse 2s ease-in-out 3}
    @keyframes hintPulse{0%,100%{background:#f3ecff}50%{background:#e4d4fc}}
    .det-panel{background:#f8f9fa;border-top:3px solid #6f42c1;padding:1rem 1.25rem;display:none;animation:detFade .2s ease}
    .det-panel.show{display:block}
    @keyframes detFade{from{opacity:0}to{opacity:1}}
    .det-panel .det-title{font-weight:700;font-size:.95rem;color:#6f42c1;margin-bottom:.4rem}
    .radio-box{background:#1a1a4e;color:#7df87d;border-radius:.5rem;padding:.65rem 1rem;font-family:'Courier New',Courier,monospace;font-weight:700;font-size:.95rem;text-align:center;letter-spacing:.3px;line-height:1.7}
    .formato-tpl{background:#fff;border:2px dashed #6f42c1;border-radius:.5rem;padding:.6rem 1rem;font-family:'Courier New',Courier,monospace;font-weight:700;font-size:.9rem;text-align:center;color:#6f42c1;letter-spacing:.3px;line-height:1.7}
    .rdl{display:flex;gap:.5rem;align-items:flex-start;padding:.35rem 0;font-size:.82rem}
    .rdl .tag{font-weight:700;font-size:.65rem;text-transform:uppercase;padding:2px 7px;border-radius:3px;white-space:nowrap;flex-shrink:0;margin-top:1px;color:#fff}
    .rdl .tag-op{background:#6f42c1}.rdl .tag-un{background:#dc3545}.rdl .tag-ok{background:#198754}
    .rdl .msg{font-family:'Courier New',Courier,monospace;font-weight:700;font-size:.85rem;color:#212529}
    .dato-mini{background:#fff;border:1px solid #dee2e6;border-radius:.5rem;text-align:center;padding:.6rem .5rem}
    .dato-mini i{font-size:1.4rem}.dato-mini .fw-bold{font-size:.8rem}.dato-mini .text-muted{font-size:.68rem}
    .dato-mini.dato-viper{border-color:#6f42c1;background:#f3ecff}
    .alerta-tono{background:#ffeaea;border:1px solid #f5c2c2;border-radius:.5rem;padding:.6rem .75rem;font-size:.78rem}
</style>

<div class="modal fade" id="modalInstructivos" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
<div class="modal-content border-0 overflow-hidden shadow">

    {{-- Header --}}
    <div class="inst-header-bg text-white px-3 px-md-4 pt-3 pb-0">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0 fw-bold"><i class="bi bi-book-fill me-2"></i>Instructivos del Operador</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <ul class="nav nav-tabs border-0" id="instructivoTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#panDespacho" type="button" role="tab">
                    <i class="bi bi-megaphone-fill me-1"></i>Despacho Normal
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#panApoyo" type="button" role="tab">
                    <i class="bi bi-people-fill me-1"></i>Despacho de Apoyo (10-12)
                </button>
            </li>
        </ul>
    </div>

    {{-- Body --}}
    <div class="modal-body p-0">
    <div class="tab-content">

        {{-- ═══════════════════════════════════════════════════
             TAB 1: DESPACHO NORMAL
        ════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade show active" id="panDespacho" role="tabpanel">

            <div class="row g-0">
                <div class="col-4">
                    <div class="paso-card-item sel" data-det="det1">
                        <span class="p-num">PASO 1</span>
                        <i class="bi bi-clipboard-check p-ico text-primary"></i>
                        <span class="p-lbl">Identifica los 4 datos<br>(Clave - Dirección - Sector - Unidades)</span>
                        <i class="bi bi-chevron-down p-chevron"></i>
                    </div>
                </div>
                <div class="col-4">
                    <div class="paso-card-item" data-det="det2">
                        <span class="p-badge" style="background:#dc3545;">×3 veces</span>
                        <span class="p-num">PASO 2</span>
                        <i class="bi bi-broadcast-pin p-ico text-danger"></i>
                        <span class="p-lbl">Lee el despacho<br>por radio</span>
                        <i class="bi bi-chevron-down p-chevron"></i>
                    </div>
                </div>
                <div class="col-4">
                    <div class="paso-card-item" data-det="det3">
                        <span class="p-badge" style="background:#198754;">×1 vez</span>
                        <span class="p-num">PASO 3</span>
                        <i class="bi bi-file-earmark-text p-ico text-success"></i>
                        <span class="p-lbl">Lee el preinforme<br>(0-1)</span>
                        <i class="bi bi-chevron-down p-chevron"></i>
                    </div>
                </div>
                <div class="col-4">
                    <div class="paso-card-item" data-det="det4">
                        <span class="p-badge" style="background:#6f42c1;"><i class="bi bi-clock"></i> 1 min</span>
                        <span class="p-num">PASO 4</span>
                        <i class="bi bi-headset p-ico" style="color:#6f42c1;"></i>
                        <span class="p-lbl">Solicita el 6-0<br>a cada unidad</span>
                        <i class="bi bi-chevron-down p-chevron"></i>
                    </div>
                </div>
                <div class="col-4">
                    <div class="paso-card-item" data-det="det5">
                        <span class="p-badge" style="background:#fd7e14;">×2 veces</span>
                        <span class="p-num">PASO 5</span>
                        <i class="bi bi-arrow-repeat p-ico" style="color:#fd7e14;"></i>
                        <span class="p-lbl">Repite el<br>despacho</span>
                        <i class="bi bi-chevron-down p-chevron"></i>
                    </div>
                </div>
                <div class="col-4">
                    <div class="paso-card-item" data-det="det6">
                        <span class="p-badge" style="background:#198754;">×1 vez</span>
                        <span class="p-num">PASO 6</span>
                        <i class="bi bi-check-circle p-ico text-success"></i>
                        <span class="p-lbl">Lee el 0-1<br>nuevamente</span>
                        <i class="bi bi-chevron-down p-chevron"></i>
                    </div>
                </div>
            </div>

            <div class="hint-bar"><i class="bi bi-hand-index-thumb me-1"></i>Toca cualquier paso para ver el detalle y ejemplo</div>

            {{-- Detalle 1 --}}
            <div class="det-panel show" id="det1">
                <div class="det-title"><i class="bi bi-1-circle-fill me-1"></i>Identifica los 4 datos del despacho</div>
                <p class="text-muted small mb-2">Necesitas reunir 4 datos antes de leer por radio. Las <strong>unidades</strong> las entrega el sistema <strong>VIPER</strong>; la <strong>clave</strong>, <strong>dirección</strong> y <strong>sector</strong> vienen del llamado de emergencia.</p>
                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <div class="dato-mini"><i class="bi bi-hash text-danger d-block"></i><div class="fw-bold">Clave</div><div class="text-muted">Ej: 10-0-1</div></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="dato-mini"><i class="bi bi-geo-alt-fill text-warning d-block"></i><div class="fw-bold">Dirección</div><div class="text-muted">Ej: Los Mañíos 1545</div></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="dato-mini"><i class="bi bi-pin-map-fill text-success d-block"></i><div class="fw-bold">Sector</div><div class="text-muted">Ej: Villa San Pedro</div></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="dato-mini dato-viper"><i class="bi bi-truck-front-fill d-block" style="color:#6f42c1;"></i><div class="fw-bold">Unidades</div><div class="text-muted">Ej: B-2, BQ-4</div><span class="badge mt-1" style="background:#6f42c1;font-size:.55rem;">VIPER</span></div>
                    </div>
                </div>
            </div>

            {{-- Detalle 2 --}}
            <div class="det-panel" id="det2">
                <div class="det-title"><i class="bi bi-2-circle-fill me-1"></i>Lee el despacho por radio <span class="badge bg-danger ms-1" style="font-size:.65rem;">3 VECES</span></div>
                <p class="text-muted small mb-2">Formato: <strong>CLAVE + DIRECCIÓN + SECTOR + UNIDADES</strong>. Lee pausado y claro:</p>
                <div class="formato-tpl mb-2"><span class="d-block mb-1" style="font-size:.7rem;color:#999;font-weight:400;">FORMATO:</span>[CLAVE] &nbsp; [DIRECCIÓN] &nbsp; SECTOR [SECTOR] &nbsp; [UNIDADES]</div>
                <div class="radio-box"><span class="d-block mb-1" style="font-size:.7rem;color:rgba(255,255,255,.5);font-weight:400;">EJEMPLO:</span>"10-0-1 &nbsp; LOS MAÑÍOS 1545 &nbsp; SECTOR VILLA SAN PEDRO &nbsp; B-2 &nbsp; BQ-4"</div>
            </div>

            {{-- Detalle 3 --}}
            <div class="det-panel" id="det3">
                <div class="det-title"><i class="bi bi-3-circle-fill me-1"></i>Lee el preinforme (0-1) <span class="badge bg-success ms-1" style="font-size:.65rem;">1 VEZ</span></div>
                <p class="text-muted small mb-2">El 0-1 describe brevemente qué está ocurriendo. Se lee <strong>una sola vez</strong>:</p>
                <div class="formato-tpl mb-2"><span class="d-block mb-1" style="font-size:.7rem;color:#999;font-weight:400;">FORMATO:</span>0-1 &nbsp; [DESCRIPCIÓN DEL INCIDENTE]</div>
                <div class="radio-box"><span class="d-block mb-1" style="font-size:.7rem;color:rgba(255,255,255,.5);font-weight:400;">EJEMPLO:</span>"0-1 &nbsp; INCENDIO EN VIVIENDA"</div>
            </div>

            {{-- Detalle 4 --}}
            <div class="det-panel" id="det4">
                <div class="det-title"><i class="bi bi-4-circle-fill me-1"></i>Solicita el 6-0 a cada unidad <span class="badge ms-1" style="font-size:.65rem;background:#6f42c1;"><i class="bi bi-clock me-1"></i>TRAS 1 MINUTO</span></div>
                <p class="text-muted small mb-2">Pregunta a cada carro. El carro responde con el oficial al mando y cantidad de voluntarios. Tú confirmas con <strong>"CONFORME"</strong> repitiendo <strong>exactamente</strong> lo que dijo.</p>
                <div class="formato-tpl mb-2"><span class="d-block mb-1" style="font-size:.7rem;color:#999;font-weight:400;">FORMATO:</span>TÚ: "6-0 [UNIDAD]?" → CARRO RESPONDE → TÚ: "CONFORME [LO QUE DIJO]"</div>
                <div class="row g-2">
                    <div class="col-12 col-md-6">
                        <div class="bg-white rounded border p-2">
                            <div class="fw-bold small text-muted mb-1"><i class="bi bi-truck-front me-1"></i>Ejemplo B-2</div>
                            <div class="rdl"><span class="tag tag-op">TÚ</span><span class="msg">"6-0 B-2?"</span></div>
                            <div class="rdl"><span class="tag tag-un">B-2</span><span class="msg">"B-2 6-0 0-12 201 4 VOL"</span></div>
                            <div class="rdl"><span class="tag tag-ok">OK</span><span class="msg">"CONFORME B-2 6-0 0-12 201 4 VOL"</span></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="bg-white rounded border p-2">
                            <div class="fw-bold small text-muted mb-1"><i class="bi bi-truck-front me-1"></i>Ejemplo BQ-4</div>
                            <div class="rdl"><span class="tag tag-op">TÚ</span><span class="msg">"6-0 BQ-4?"</span></div>
                            <div class="rdl"><span class="tag tag-un">BQ-4</span><span class="msg">"BQ-4 6-0 0-12 VOL J.VERGARA 6 VOL"</span></div>
                            <div class="rdl"><span class="tag tag-ok">OK</span><span class="msg">"CONFORME BQ-4 6-0 0-12 VOL J.VERGARA 6 VOL"</span></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detalle 5 --}}
            <div class="det-panel" id="det5">
                <div class="det-title"><i class="bi bi-5-circle-fill me-1"></i>Repite el despacho <span class="badge ms-1" style="font-size:.65rem;background:#fd7e14;">2 VECES</span></div>
                <p class="text-muted small mb-2">Con todos los 6-0 listos, vuelve a leer el despacho completo <strong>2 veces</strong>:</p>
                <div class="formato-tpl mb-2"><span class="d-block mb-1" style="font-size:.7rem;color:#999;font-weight:400;">FORMATO:</span>[CLAVE] &nbsp; [DIRECCIÓN] &nbsp; SECTOR [SECTOR] &nbsp; [UNIDADES]</div>
                <div class="radio-box"><span class="d-block mb-1" style="font-size:.7rem;color:rgba(255,255,255,.5);font-weight:400;">EJEMPLO:</span>"10-0-1 &nbsp; LOS MAÑÍOS 1545 &nbsp; SECTOR VILLA SAN PEDRO &nbsp; B-2 &nbsp; BQ-4"</div>
            </div>

            {{-- Detalle 6 --}}
            <div class="det-panel" id="det6">
                <div class="det-title"><i class="bi bi-6-circle-fill me-1"></i>Lee el preinforme una vez más <span class="badge bg-success ms-1" style="font-size:.65rem;">1 VEZ</span></div>
                <p class="text-muted small mb-2">Para cerrar el despacho, lee nuevamente el 0-1:</p>
                <div class="formato-tpl mb-2"><span class="d-block mb-1" style="font-size:.7rem;color:#999;font-weight:400;">FORMATO:</span>0-1 &nbsp; [DESCRIPCIÓN DEL INCIDENTE]</div>
                <div class="radio-box mb-3"><span class="d-block mb-1" style="font-size:.7rem;color:rgba(255,255,255,.5);font-weight:400;">EJEMPLO:</span>"0-1 &nbsp; INCENDIO EN VIVIENDA"</div>
                <div class="text-center">
                    <span class="d-inline-flex align-items-center gap-2 rounded-pill px-3 py-2" style="background:rgba(25,135,84,.1);">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <span class="fw-bold text-success">¡Despacho completado!</span>
                    </span>
                </div>
            </div>

        </div>{{-- fin panDespacho --}}

        {{-- ═══════════════════════════════════════════════════
             TAB 2: DESPACHO DE APOYO (10-12)
        ════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="panApoyo" role="tabpanel">

            {{-- Alerta tono de despacho --}}
            <div class="px-3 pt-3 pb-0">
                <div class="alerta-tono d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-diamond-fill text-danger flex-shrink-0 mt-1" style="font-size:1.1rem;"></i>
                    <span>
                        <strong>Cuando selecciones el tono:</strong>
                        Selecciona la <strong>compañía que prestará el apoyo</strong> y
                        usa <strong>siempre el tono de INCENDIO</strong>, sin importar el tipo de emergencia
                        (rescate, accidente, HazMat, etc.).
                    </span>
                </div>
            </div>

            <div class="row g-0 mt-2">
                <div class="col-4">
                    <div class="paso-card-item sel" data-det="detA1">
                        <span class="p-num">PASO 1</span>
                        <i class="bi bi-clipboard-check p-ico text-primary"></i>
                        <span class="p-lbl">Identifica los 3 datos<br>(10-12 - CB Solicitante - Unidad)</span>
                        <i class="bi bi-chevron-down p-chevron"></i>
                    </div>
                </div>
                <div class="col-4">
                    <div class="paso-card-item" data-det="detA2">
                        <span class="p-badge" style="background:#dc3545;">×3 veces</span>
                        <span class="p-num">PASO 2</span>
                        <i class="bi bi-broadcast-pin p-ico text-danger"></i>
                        <span class="p-lbl">Lee el despacho<br>por radio</span>
                        <i class="bi bi-chevron-down p-chevron"></i>
                    </div>
                </div>
                <div class="col-4">
                    <div class="paso-card-item" data-det="detA3">
                        <span class="p-badge" style="background:#198754;">×1 vez</span>
                        <span class="p-num">PASO 3</span>
                        <i class="bi bi-file-earmark-text p-ico text-success"></i>
                        <span class="p-lbl">Lee el preinforme<br>(0-1)</span>
                        <i class="bi bi-chevron-down p-chevron"></i>
                    </div>
                </div>
                <div class="col-4">
                    <div class="paso-card-item" data-det="detA4">
                        <span class="p-badge" style="background:#6f42c1;"><i class="bi bi-clock"></i> 1 min</span>
                        <span class="p-num">PASO 4</span>
                        <i class="bi bi-headset p-ico" style="color:#6f42c1;"></i>
                        <span class="p-lbl">Solicita el 6-0<br>a la unidad</span>
                        <i class="bi bi-chevron-down p-chevron"></i>
                    </div>
                </div>
                <div class="col-4">
                    <div class="paso-card-item" data-det="detA5">
                        <span class="p-badge" style="background:#fd7e14;">×2 veces</span>
                        <span class="p-num">PASO 5</span>
                        <i class="bi bi-arrow-repeat p-ico" style="color:#fd7e14;"></i>
                        <span class="p-lbl">Repite el<br>despacho</span>
                        <i class="bi bi-chevron-down p-chevron"></i>
                    </div>
                </div>
                <div class="col-4">
                    <div class="paso-card-item" data-det="detA6">
                        <span class="p-badge" style="background:#198754;">×1 vez</span>
                        <span class="p-num">PASO 6</span>
                        <i class="bi bi-check-circle p-ico text-success"></i>
                        <span class="p-lbl">Lee el 0-1<br>nuevamente</span>
                        <i class="bi bi-chevron-down p-chevron"></i>
                    </div>
                </div>
            </div>

            <div class="hint-bar"><i class="bi bi-hand-index-thumb me-1"></i>Toca cualquier paso para ver el detalle y ejemplo</div>

            {{-- Detalle A1 --}}
            <div class="det-panel show" id="detA1">
                <div class="det-title"><i class="bi bi-1-circle-fill me-1"></i>Identifica los 3 datos del despacho de apoyo</div>
                <p class="text-muted small mb-2">El CB solicitante indica qué tipo de unidad necesita. La <strong>clave siempre es 10-12</strong>.</p>
                <div class="row g-2">
                    <div class="col-6 col-md-4">
                        <div class="dato-mini"><i class="bi bi-hash text-danger d-block"></i><div class="fw-bold">Clave</div><div class="text-muted">Siempre: <strong>10-12</strong></div></div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="dato-mini"><i class="bi bi-building text-warning d-block"></i><div class="fw-bold">CB Solicitante</div><div class="text-muted">Ej: CB Concepción</div></div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="dato-mini"><i class="bi bi-truck-front-fill text-primary d-block"></i><div class="fw-bold">Unidad en Apoyo</div><div class="text-muted">Ej: R-3</div></div>
                    </div>
                </div>
            </div>

            {{-- Detalle A2 --}}
            <div class="det-panel" id="detA2">
                <div class="det-title"><i class="bi bi-2-circle-fill me-1"></i>Lee el despacho por radio <span class="badge bg-danger ms-1" style="font-size:.65rem;">3 VECES</span></div>
                <p class="text-muted small mb-2">Formato: <strong>CLAVE + CB SOLICITANTE + UNIDAD</strong>. Lee pausado y claro:</p>
                <div class="formato-tpl mb-2"><span class="d-block mb-1" style="font-size:.7rem;color:#999;font-weight:400;">FORMATO:</span>10-12 &nbsp; CUERPO DE BOMBEROS [NOMBRE] &nbsp; [UNIDAD]</div>
                <div class="radio-box"><span class="d-block mb-1" style="font-size:.7rem;color:rgba(255,255,255,.5);font-weight:400;">EJEMPLO:</span>"10-12 &nbsp; CUERPO DE BOMBEROS CONCEPCIÓN &nbsp; R-3"</div>
            </div>

            {{-- Detalle A3 --}}
            <div class="det-panel" id="detA3">
                <div class="det-title"><i class="bi bi-3-circle-fill me-1"></i>Lee el preinforme (0-1) <span class="badge bg-success ms-1" style="font-size:.65rem;">1 VEZ</span></div>
                <p class="text-muted small mb-2">Describe el motivo del apoyo. Se lee <strong>una sola vez</strong>:</p>
                <div class="formato-tpl mb-2"><span class="d-block mb-1" style="font-size:.7rem;color:#999;font-weight:400;">FORMATO:</span>0-1 &nbsp; APOYO A CUERPO DE BOMBEROS [NOMBRE] POR [MOTIVO]</div>
                <div class="radio-box"><span class="d-block mb-1" style="font-size:.7rem;color:rgba(255,255,255,.5);font-weight:400;">EJEMPLO:</span>"0-1 &nbsp; APOYO A CB CONCEPCIÓN POR COLISIÓN DE 2 VEHÍCULOS MENORES"</div>
            </div>

            {{-- Detalle A4 --}}
            <div class="det-panel" id="detA4">
                <div class="det-title"><i class="bi bi-4-circle-fill me-1"></i>Solicita el 6-0 a la unidad <span class="badge ms-1" style="font-size:.65rem;background:#6f42c1;"><i class="bi bi-clock me-1"></i>TRAS 1 MINUTO</span></div>
                <p class="text-muted small mb-2">Igual que en un despacho normal. Pregunta, el carro responde, tú confirmas con <strong>"CONFORME"</strong>.</p>
                <div class="formato-tpl mb-2"><span class="d-block mb-1" style="font-size:.7rem;color:#999;font-weight:400;">FORMATO:</span>TÚ: "6-0 [UNIDAD]?" → CARRO RESPONDE → TÚ: "CONFORME [LO QUE DIJO]"</div>
                <div class="bg-white rounded border p-2">
                    <div class="fw-bold small text-muted mb-1"><i class="bi bi-truck-front me-1"></i>Ejemplo R-3</div>
                    <div class="rdl"><span class="tag tag-op">TÚ</span><span class="msg">"6-0 R-3?"</span></div>
                    <div class="rdl"><span class="tag tag-un">R-3</span><span class="msg">"R-3 6-0 0-12 VOL MUÑOZ 5 VOL"</span></div>
                    <div class="rdl"><span class="tag tag-ok">OK</span><span class="msg">"CONFORME R-3 6-0 0-12 VOL MUÑOZ 5 VOL"</span></div>
                </div>
            </div>

            {{-- Detalle A5 --}}
            <div class="det-panel" id="detA5">
                <div class="det-title"><i class="bi bi-5-circle-fill me-1"></i>Repite el despacho <span class="badge ms-1" style="font-size:.65rem;background:#fd7e14;">2 VECES</span></div>
                <p class="text-muted small mb-2">Con el 6-0 listo, vuelve a leer el despacho completo <strong>2 veces</strong>:</p>
                <div class="radio-box"><span class="d-block mb-1" style="font-size:.7rem;color:rgba(255,255,255,.5);font-weight:400;">EJEMPLO:</span>"10-12 &nbsp; CUERPO DE BOMBEROS CONCEPCIÓN &nbsp; R-3"</div>
            </div>

            {{-- Detalle A6 --}}
            <div class="det-panel" id="detA6">
                <div class="det-title"><i class="bi bi-6-circle-fill me-1"></i>Lee el preinforme una vez más <span class="badge bg-success ms-1" style="font-size:.65rem;">1 VEZ</span></div>
                <p class="text-muted small mb-2">Para cerrar el despacho de apoyo, lee nuevamente el 0-1:</p>
                <div class="radio-box mb-3"><span class="d-block mb-1" style="font-size:.7rem;color:rgba(255,255,255,.5);font-weight:400;">EJEMPLO:</span>"0-1 &nbsp; APOYO A CB CONCEPCIÓN POR COLISIÓN DE 2 VEHÍCULOS MENORES"</div>
                <div class="text-center">
                    <span class="d-inline-flex align-items-center gap-2 rounded-pill px-3 py-2" style="background:rgba(25,135,84,.1);">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <span class="fw-bold text-success">¡Despacho de apoyo completado!</span>
                    </span>
                </div>
            </div>

        </div>{{-- fin panApoyo --}}

    </div>{{-- fin tab-content --}}
    </div>{{-- fin modal-body --}}

    <div class="modal-footer py-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i>Cerrar</button>
    </div>

</div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('modalInstructivos');
    if (!modal) return;

    function initGrid(panelId) {
        var panel = document.getElementById(panelId);
        if (!panel) return null;
        var cards   = panel.querySelectorAll('.paso-card-item');
        var dets    = panel.querySelectorAll('.det-panel');

        function activar(card) {
            cards.forEach(function(c) { c.classList.remove('sel'); });
            dets.forEach(function(d) { d.classList.remove('show'); });
            card.classList.add('sel');
            var t = document.getElementById(card.getAttribute('data-det'));
            if (t) t.classList.add('show');
        }

        cards.forEach(function(card) {
            card.addEventListener('click', function() { activar(card); });
        });

        return function() { if (cards[0]) activar(cards[0]); };
    }

    var resetDespacho = initGrid('panDespacho');
    var resetApoyo    = initGrid('panApoyo');

    modal.addEventListener('show.bs.modal', function () {
        if (resetDespacho) resetDespacho();
        if (resetApoyo) resetApoyo();
    });
});
</script>