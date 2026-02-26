function C(){if(typeof $>"u"){setTimeout(C,100);return}$(document).ready(function(){const x=$(".globalSearch"),s=$("#searchModal"),i=$("#modalSearchInput"),l=$("#modalSearchLoader"),d=$("#quickActions"),o=$("#searchResultsSection"),f=$("#modalSearchResultsContent"),h=$("#modalSearchResultsCount"),r=$("#emptySearchState");let g,c;y();function y(){x.on("click focus",function(t){t.preventDefault(),e()}),$(document).on("keydown",function(t){(t.metaKey||t.ctrlKey)&&t.key==="k"&&(t.preventDefault(),e())});function e(){s.modal("show")}s.on("shown.bs.modal",function(){setTimeout(function(){i[0].focus()},100),m()}),s.on("hidden.bs.modal",function(){i.val(""),m(),c&&c.abort()}),i.on("input",function(){const t=$(this).val().trim();if(clearTimeout(g),c&&c.abort(),t.length===0){m();return}if(t.length<2){v();return}l.removeClass("d-none"),g=setTimeout(()=>{S(t)},300)}),i.on("keydown",function(t){E(t)}),$(document).on("click",".modal-search-result-item",function(){s.modal("hide")})}function S(e){w(),c=$.ajax({url:"/admin/search",method:"GET",data:{q:e,limit:15},success:function(t){p(),t.success?k(t.results,e):b("Search failed. Please try again.")},error:function(t){p(),t.statusText!=="abort"&&b("Search failed. Please check your connection.")}})}function m(){d.removeClass("d-none"),o.addClass("d-none"),r.addClass("d-none"),l.addClass("d-none")}function v(){d.addClass("d-none"),o.addClass("d-none"),r.removeClass("d-none"),l.addClass("d-none")}function w(){l.removeClass("d-none")}function p(){l.addClass("d-none")}function k(e,t){if(h.text(`${e.length} result${e.length!==1?"s":""}`),e.length===0){v();return}d.addClass("d-none"),r.addClass("d-none"),o.removeClass("d-none");let a="";if(e.length>0){const n=M(e);Object.keys(n).forEach((u,D)=>{D>0&&(a+='<hr class="my-3">'),a+=`
                    <div class="mb-4">
                        <h6 class="mb-3 text-uppercase text-muted fw-semibold small">
                            ${u}
                        </h6>
                        <div class="d-flex flex-column gap-1">
                `,n[u].forEach(j=>{a+=R(j,t)}),a+=`
                        </div>
                    </div>
                `}),e.length>=15&&(a+=`
                    <hr class="my-3">
                    <div class="text-center">
                        <small class="text-muted">Showing first 15 results</small>
                    </div>
                `),f.html(a)}typeof lucide<"u"&&lucide.createIcons()}function M(e){const t={};return e.forEach(a=>{const n=a.category||"Other";t[n]||(t[n]=[]),t[n].push(a)}),t}function R(e,t){const a=e.avatar?`<img src="${e.avatar}" alt="${e.title}" class="rounded-circle avatar-sm">`:`<div class="d-flex align-items-center justify-content-center rounded-circle bg-light avatar-sm">
                <i data-lucide="${e.icon||"file"}" class="text-muted"></i>
            </div>`,n=e.badge?`<span class="badge bg-light text-dark small">${e.badge}</span>`:"",u=e.status?`<span class="badge ${e.status.toLowerCase()==="active"?"bg-success":"bg-danger"} small">${e.status}</span>`:"";return`
            <a href="${e.url}" class="modal-search-result-item d-flex align-items-center py-2 px-3 text-decoration-none rounded border-0" data-url="${e.url}">
                ${a}
                <div class="flex-grow-1 ms-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-medium small">
                            ${I(e.title,t)}
                        </span>
                        ${n}
                        ${u}
                    </div>
                  
                    ${e.description?`
                        <div class="text-muted small opacity-75">
                            ${e.description}
                        </div>
                    `:""}
                </div>
                <i data-lucide="external-link" class="icon-sm text-muted ms-2"></i>
            </a>
        `}function b(e){h.text("Error"),d.addClass("d-none"),r.addClass("d-none"),o.removeClass("d-none"),f.html(`
            <div class="text-center py-4">
                <div class="d-flex justify-content-center mb-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 avatar-lg">
                        <i data-lucide="alert-circle" class="icon-lg text-danger"></i>
                    </div>
                </div>
                <h6 class="mb-2 text-danger fw-semibold">Something went wrong</h6>
                <p class="mb-0 text-muted small">${e}</p>
            </div>
        `),typeof lucide<"u"&&lucide.createIcons()}function E(e){const t=o.find(".modal-search-result-item"),a=t.filter(".active");switch(e.keyCode){case 40:if(e.preventDefault(),a.length===0)t.first().addClass("active bg-light");else{const n=a.removeClass("active bg-light").next(".modal-search-result-item");n.length>0?n.addClass("active bg-light"):t.first().addClass("active bg-light")}break;case 38:if(e.preventDefault(),a.length===0)t.last().addClass("active bg-light");else{const n=a.removeClass("active bg-light").prev(".modal-search-result-item");n.length>0?n.addClass("active bg-light"):t.last().addClass("active bg-light")}break;case 13:if(e.preventDefault(),a.length>0){const n=a.attr("href");n&&(window.location.href=n)}break;case 27:e.preventDefault(),s.modal("hide");break}}function I(e,t){if(!t||!e)return e;const a=new RegExp(`(${T(t)})`,"gi");return e.replace(a,'<mark class="bg-warning bg-opacity-25 text-dark fw-semibold rounded-1">$1</mark>')}function T(e){return e.replace(/[.*+?^${}()|[\]\\]/g,"\\$&")}})}C();
