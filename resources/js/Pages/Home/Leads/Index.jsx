import React, { useState, useEffect } from 'react';
import DataTable from "react-data-table-component";
import isEmpty from 'lodash/isEmpty';
import { toast, ToastContainer } from "react-toastify";
import { toastConfig } from "../../../Common/Toaster/Toast.config";

const DEFAULT_TOTAL_ROWS = 0;
const DEFAULT_PAGE = 1;
const DEFAULT_ITEMS_PER_PAGE = 10;
const POLLING_TIMEOUT = 1000;
const MAX_RETRIES = 3;
let retry = 1;
let polling = null;

export default function Lead(props) {
    const axios = window.axios;
    const [leads, setLeads] = useState([]);
    const [loading, setLoading] = useState(false);
    const [totalRows, setTotalRows] = useState(DEFAULT_TOTAL_ROWS);
    const [itemsPerPage, setItemsPerPage] = useState(DEFAULT_ITEMS_PER_PAGE);
    const [isExporting, setIsExporting] = useState(false);
    const [exportId, setExportId] = useState(null);
    const { userInfo } = props;

    const columns = [
        {
            name: 'First Name',
            selector: row => row.FirstName
        },
        {
            name: 'Last Name',
            selector: row => row.LastName
        },
        {
            name: 'Email',
            selector: row => row.Email
        },
        {
            name: 'Phone',
            selector: row => row.Phone
        }
    ];

    const fetchLeads = async page => {
        setLoading(true);

        const response = await axios.get(`/api/v1/leads?page=${page}&itemsPerPage=${itemsPerPage}`);

        setLeads(response.data.records);
        setTotalRows(response.data.totalRecords);
        setLoading(false);
    };

    const handlePageChange = page => fetchLeads(page);

    const handlePerRowsChange = async (newPerPage, page) => {
        setLoading(true);

        const response = await axios.get(`/api/v1/leads?page=${page}&itemsPerPage=${newPerPage}`);

        setLeads(response.data.records);
        setItemsPerPage(newPerPage);
        setLoading(false);
    };

    const pollingExportResult = async () => {
        const response = await axios.post(`/api/v1/leads/export/${exportId}/result`);

        if (response.data) {
            setExportId(null);
            setIsExporting(false);
            clearTimeout(polling);
            const url = window.URL.createObjectURL(new Blob([response.data]))
            const link = document.createElement('a')
            link.href = url
            link.setAttribute('download', "leads.csv")
            document.body.appendChild(link)
            link.click()
            link.remove()
        } else {
            ++retry;
            if (retry > MAX_RETRIES) {
                setExportId(null);
                setIsExporting(false);
                clearTimeout(polling);
                toast.error(`Failed to download the CSV file. Number of retries: ${retry}. Please try to export again.`, toastConfig);
            } else {
                polling = setTimeout(pollingExportResult, POLLING_TIMEOUT);
            }
        }
    }

    const exportData = async () => {
        setIsExporting(true);

        const response = await axios.post(`/api/v1/leads/export`);

        const { data: { success, message, result } } = response;

        if (success) {
            toast.success(message, toastConfig);
            setExportId(result.exportId);
        } else {
            toast.error(message, toastConfig);
        }
    }

    useEffect(() => {
        fetchLeads(DEFAULT_PAGE);
    }, []);

    useEffect(() => {
        if (exportId) {
            polling = setTimeout(pollingExportResult, POLLING_TIMEOUT);
        }
    }, [exportId])

    return (
        <div className="container">
            <div className="row">
                <div className={!isEmpty(userInfo) ? 'col-md-3' : 'col-md-12'}>
                    <h1 className="my-3 fw-bold">Leads</h1>
                </div>
                {!isEmpty(userInfo) && <div className="col-md-9">
                    <div className="d-flex h-100 align-items-center justify-content-end">
                        <button className="btn btn-primary px-5"
                                disabled={isExporting || loading}
                                onClick={exportData}>
                            {isExporting &&
                                <span className="spinner-border spinner-border-sm me-1" role="status"
                                      aria-hidden="true"></span>}
                            {isExporting ? 'Exporting...' : 'Export'}
                        </button>
                    </div>
                </div>}
            </div>

            {isEmpty(userInfo) && <div className="row">
                <p>Please connect to your SFDC Account in the tab Connections, then you will be able to see your Leads data.</p>
            </div>
            }

            {!isEmpty(userInfo) &&
                <DataTable
                    columns={columns}
                    data={leads}
                    progressPending={loading}
                    pagination
                    paginationServer
                    paginationTotalRows={totalRows}
                    onChangeRowsPerPage={handlePerRowsChange}
                    onChangePage={handlePageChange}
                />
            }
            <ToastContainer />
        </div>
    )
}
