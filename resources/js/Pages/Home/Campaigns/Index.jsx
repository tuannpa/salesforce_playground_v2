import React, { useEffect, useState, useRef } from 'react';
import { Field, Formik } from "formik";
import isEmpty from 'lodash/isEmpty';
import values from 'lodash/values';
import cloneDeep from 'lodash/cloneDeep';
import DataTable from "react-data-table-component";
import { ToastContainer } from "react-toastify";
import MultiSelectWithCheckbox from "../../../Common/Components/MultiSelectWithCheckbox";
import apiInstance from "../../../Common/API/instance";
import Chart from "./Popup/Chart";

const DEFAULT_TOTAL_ROWS = 0;
const DEFAULT_PAGE = 1;
const DEFAULT_ITEMS_PER_PAGE = 10;

const statusOptions = [
    { label: 'Planned', value: 'planned' },
    { label: 'In progress', value: 'in progress' },
    { label: 'Completed', value: 'completed' }
];

const typeOptions = [
    { label: 'Webinar', value: 'webinar' },
    { label: 'Partners', value: 'partners' },
    { label: 'Email', value: 'email' },
    { label: 'Conference', value: 'conference' },
    { label: 'Direct Mail', value: 'direct mail' },
    { label: 'Trade Show', value: 'trade show' }
];

export default function Campaign(props) {
    const { userInfo } = props;
    const [isOpenChartModal, setIsOpenChartModal] = useState(false);
    const [loading, setLoading] = useState(false);
    const [campaigns, setCampaigns] = useState([]);
    const [totalRows, setTotalRows] = useState(DEFAULT_TOTAL_ROWS);
    const [itemsPerPage, setItemsPerPage] = useState(DEFAULT_ITEMS_PER_PAGE);
    const [currentPage, setCurrentPage] = useState(DEFAULT_PAGE);
    const [currentFilters, setCurrentFilters] = useState({});
    const [selectedCampaignId, setSelectedCampaignId] = useState(null);
    const isFirstRender = useRef(true);

    const columns = [
        {
            name: 'ID',
            selector: row => row.Id,
            cell: row => <a href={getCampaignDetailsURL(row.Id)} target="_blank">{row.Id}</a>
        },
        {
            name: 'Name',
            selector: row => row.Name
        },
        {
            name: 'Start Date',
            selector: row => row.StartDate
        },
        {
            name: 'End Date',
            selector: row => row.EndDate
        },
        {
            name: 'Status',
            selector: row => row.Status
        },
        {
            name: 'Type',
            selector: row => row.Type
        },
        {
            name: 'Action',
            cell: row => <button className="btn btn-outline-primary" onClick={() => {
                setSelectedCampaignId(row.Id);
                setIsOpenChartModal(true);
            }}>Analytics</button>
        }
    ];

    const getCampaignDetailsURL = campaignId => {
        const { instanceUri } = userInfo;

        return `${instanceUri}/${campaignId}`;
    };

    const handlePageChange = async page => {
        setCurrentPage(page);
    };

    const handlePerRowsChange = async (newPerPage, page) => {
        setLoading(true);
        if (page !== currentPage) {
            setCurrentPage(page);
        }

        const response = await apiInstance.get(`/campaigns?page=${page}&itemsPerPage=${newPerPage}`);

        setCampaigns(response.data.records);
        setItemsPerPage(newPerPage);
        setLoading(false);
    };

    const onChartModalClose = () => setSelectedCampaignId(null);

    const fetchCampaigns = async () => {
        setLoading(true);

        const filters = cloneDeep(currentFilters);
        const filterObj = {};

        Object.entries(filters).forEach(([key, value]) => {
            if (!isEmpty(value)) {
                filterObj[key] = value.join(',')
            }
        });
        const filter = new URLSearchParams(filterObj).toString();
        let endpoint = `/campaigns?page=${currentPage}&itemsPerPage=${itemsPerPage}`;
        if (filter) {
            endpoint += `&${filter}`;
        }

        const response = await apiInstance.get(endpoint);

        setCampaigns(response.data.records);
        setTotalRows(response.data.totalRecords);
        setLoading(false);
    };

    const searchCampaigns = filter => {
        if (values(filter).every(isEmpty)) {
            setCurrentFilters({});
        } else {
            setCurrentFilters(filter);
        }
    };

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }
        fetchCampaigns();
    }, [currentPage, currentFilters])

    useEffect(() => {
        if (!isEmpty(userInfo)) {
            fetchCampaigns();
        }
    }, []);

    return (
        <div className="container">
            <div className="row">
                <div className="col-12">
                    <h1 className="my-3 fw-bold">Campaign Details</h1>
                </div>
            </div>

            {/*Filter section*/}
            {!isEmpty(userInfo) && <div className="row mt-2 mb-5">
                <Formik
                    initialValues={{status: [], type: []}}
                    onSubmit={(values) => searchCampaigns(values)
                    }
                >
                    {({
                          handleSubmit,
                          isSubmitting,
                          /* and other goodies */
                      }) => (
                        <form onSubmit={handleSubmit}>
                            <div className="row">
                                <div className="col-md-5">
                                    <div className="row">
                                        <label htmlFor="status" className="col-md-2 col-form-label text-center">Status</label>

                                        <Field name="status"
                                               id="status"
                                               placeholder="Please select Status"
                                               component={MultiSelectWithCheckbox}
                                               options={statusOptions}
                                               className="col-md-10" />
                                    </div>
                                </div>
                                <div className="col-md-5">
                                    <div className="row">
                                        <label htmlFor="type" className="col-md-2 col-form-label text-center">Type</label>

                                        <Field name="type"
                                               id="type"
                                               placeholder="Please select Type"
                                               component={MultiSelectWithCheckbox}
                                               options={typeOptions}
                                               className="col-md-10" />
                                    </div>
                                </div>

                                <div className="col-md-2">
                                    <button className="btn btn-primary px-5" type="submit"
                                            disabled={loading}>
                                        {loading &&
                                            <span className="spinner-border spinner-border-sm me-1" role="status"
                                                  aria-hidden="true"></span>}
                                        {loading ? 'Searching...' : 'Search'}
                                    </button>
                                </div>
                            </div>
                        </form>
                    )}
                </Formik>
            </div>}
            {/*End Filter section*/}

            {isEmpty(userInfo) && <div className="row">
                    <p>Please connect to your SFDC Account in the tab Connections, then you will be able to see your Campaigns data.</p>
                </div>
            }

            {/*List of campaigns*/}
            {!isEmpty(userInfo) &&
                <DataTable
                    columns={columns}
                    data={campaigns}
                    progressPending={loading}
                    pagination
                    paginationServer
                    paginationTotalRows={totalRows}
                    onChangeRowsPerPage={handlePerRowsChange}
                    onChangePage={handlePageChange}
                />
            }
            {/*End list of campaigns*/}

            <Chart campaignId={selectedCampaignId}
                   isOpen={isOpenChartModal}
                   onChartModalClose={onChartModalClose}
                   setIsOpen={setIsOpenChartModal} />
            <ToastContainer />
        </div>
    )
}
