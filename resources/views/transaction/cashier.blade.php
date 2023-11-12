<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Cashier</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-primary" id="btnNew" onclick="btnNewOnclick(this)"><i class="fas fa-file"></i></button>
            <button type="button" class="btn btn-outline-primary" id="btnSave" onclick="btnSaveOnclick(this)"><i class="fas fa-save"></i></button>
            <button type="button" class="btn btn-outline-primary" id="btnImport" onclick="btnShowImportDataModal()" title="Import"><i class="fas fa-file-import"></i></button>
        </div>
    </div>
</div>
<form id="cashier-form">
    <div class="row">
        <div class="col mb-1" id="div-alert">
        </div>
    </div>
    <div class="row">
        <div class="col">
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link active" id="nav-input-tab" data-bs-toggle="tab" data-bs-target="#nav-input" type="button" role="tab">Transaction</button>
                    <button class="nav-link" id="nav-history-tab" data-bs-toggle="tab" data-bs-target="#nav-history" type="button" role="tab">History</button>
                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-input" role="tabpanel" aria-labelledby="nav-input-tab" tabindex="0">
                    <div class="container-fluid mt-2 border-start border-bottom rounded-start">
                        <div class="row">
                            <div class="col-md-6 mb-1">
                                <label class="form-label" for="cashierCode">Reference</label>
                                <div class="input-group">
                                    <input type="text" id="cashierCode" class="form-control">
                                    <button class="btn btn-primary" type="button" onclick="btnShowCoaModal()"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-1">
                                <label class="form-label" for="cashierName">User</label>
                                <input type="text" id="cashierName" class="form-control" maxlength="50">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-1">
                                <label class="form-label" for="cashierDate">Date</label>
                                <div class="input-group">
                                    <input type="text" id="cashierDate" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-md-6 mb-1">
                                <label class="form-label" for="cashierAmount">Amount</label>
                                <input type="text" id="cashierAmount" class="form-control">
                            </div>
                        </div>
                        <input type="hidden" id="cashierInputMode" value="0">
                    </div>
                </div>
                <div class="tab-pane fade" id="nav-history" role="tabpanel" aria-labelledby="nav-history-tab" tabindex="1">
                    <div class="container-fluid mt-2 border-start border-bottom rounded-start">

                    </div>
                </div>
            </div>
        </div>
    </div>

</form>
<!-- Modal -->
<div class="modal fade" id="coaModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Reference List</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col mb-1">
                            <div class="input-group input-group-sm mb-1">
                                <span class="input-group-text">Search by</span>
                                <select id="coaSearchBy" class="form-select" onchange="coaSearch.focus()">
                                    <option value="0">Document</option>
                                    <option value="1">User</option>
                                </select>
                                <input type="text" id="coaSearch" class="form-control" maxlength="50" onkeypress="coaSearchOnKeypress(event)">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="table-responsive" id="coaTabelContainer">
                                <table id="coaTabel" class="table table-sm table-striped table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">Document</th>
                                            <th class="text-center">User</th>
                                            <th class="text-center">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $("#cashierDate").datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        uiLibrary: 'bootstrap5'
    })
    cashierDate.value = moment().format('YYYY-MM-DD')

    Inputmask({
        'alias': 'decimal',
        'groupSeparator': ',',
    }).mask(cashierAmount);

    function btnSaveOnclick(pthis) {
        const data = {
            CCASHIER_REFF_DOC: cashierCode.value.trim(),
            CCASHIER_USER: cashierName.value.trim(),
            CCASHIER_PRICE: cashierAmount.inputmask ? cashierAmount.inputmask.unmaskedvalue() : cashierAmount.value.trim(),
            CCASHIER_ISSUDT: cashierDate.value.trim(),
            _token: '{{ csrf_token() }}',
        }
        if(data.CCASHIER_PRICE==0)
        {
            cashierAmount.focus()
            alertify.warning('Amount should not be zero')
            return
        }
        if (confirm(`Are you sure ?`)) {
            pthis.innerHTML = `Please wait...`
            pthis.disabled = true
            $.ajax({
                type: "post",
                url: "cashier",
                data: data,
                dataType: "json",
                success: function(response) {
                    pthis.innerHTML = `<i class="fas fa-save"></i>`
                    alertify.success(response.msg)
                    pthis.disabled = false
                    document.getElementById('div-alert').innerHTML = ''
                    cashierCode.value = ''
                    cashierName.value = ''
                    Inputmask.setValue(cashierAmount, 0)
                },
                error: function(xhr, xopt, xthrow) {
                    const respon = Object.keys(xhr.responseJSON)
                    const div_alert = document.getElementById('div-alert')
                    let msg = ''
                    for (const item of respon) {
                        msg += `<p>${xhr.responseJSON[item]}</p>`
                    }
                    div_alert.innerHTML = `<div class="alert alert-warning alert-dismissible fade show" role="alert">
                ${msg}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`
                    pthis.innerHTML = `<i class="fas fa-save"></i>`
                    alertify.warning(xthrow);
                    pthis.disabled = false
                }
            });
        }
    }

    function btnShowCoaModal() {
        const myModal = new bootstrap.Modal(document.getElementById('coaModal'), {})
        coaModal.addEventListener('shown.bs.modal', () => {
            coaSearch.focus()
        })
        myModal.show()
    }

    function coaSearchOnKeypress(e) {
        if (e.key === 'Enter') {
            e.target.disabled = true
            const data = {
                searchBy: coaSearchBy.value,
                searchValue: e.target.value,
            }
            coaTabel.getElementsByTagName('tbody')[0].innerHTML = `<tr><td colspan="3">Please wait</td></tr>`
            $.ajax({
                type: "GET",
                url: "SPK",
                data: data,
                dataType: "json",
                success: function(response) {
                    e.target.disabled = false
                    let myContainer = document.getElementById("coaTabelContainer");
                    let myfrag = document.createDocumentFragment();
                    let cln = coaTabel.cloneNode(true);
                    myfrag.appendChild(cln);
                    let myTable = myfrag.getElementById("coaTabel");
                    let myTableBody = myTable.getElementsByTagName("tbody")[0];
                    myTableBody.innerHTML = ''
                    response.data.forEach((arrayItem) => {
                        newrow = myTableBody.insertRow(-1)
                        newcell = newrow.insertCell(0)
                        newcell.innerHTML = arrayItem['CSPK_DOCNO']
                        newcell.style.cssText = 'cursor:pointer'
                        newcell.onclick = () => {
                            $('#coaModal').modal('hide')
                            cashierCode.value = arrayItem['CSPK_DOCNO']
                            cashierName.value = arrayItem['USER_PIC_NAME']
                            Inputmask.setValue(cashierAmount, numeral(arrayItem['TOTAL_AMOUNT']).value())
                        }
                        newcell = newrow.insertCell(-1)
                        newcell.innerHTML = arrayItem['USER_PIC_NAME']
                        newcell = newrow.insertCell(-1)
                        newcell.classList.add('text-end')
                        newcell.innerHTML = numeral(arrayItem['TOTAL_AMOUNT']).format('0,0.00')
                    })
                    myContainer.innerHTML = ''
                    myContainer.appendChild(myfrag)
                },
                error: function(xhr, xopt, xthrow) {
                    alertify.warning(xthrow);
                    e.target.disabled = false
                    coaTabel.getElementsByTagName('tbody')[0].innerHTML = `<tr><td colspan="3">Please try again</td></tr>`
                }
            });
        }
    }
</script>