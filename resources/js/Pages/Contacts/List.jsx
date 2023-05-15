import React, { useState, useEffect, useCallback } from 'react';
import DataTable from 'react-data-table-component';
import { ToastContainer, toast } from 'react-toastify';
import cloneDeep from 'lodash/cloneDeep';
import debounce from 'lodash/debounce';
import './EditableTextCell';
import EditableTextCell from "./EditableTextCell";

const DEFAULT_PAGE = 1;

const toastConfig = {
    position: "bottom-right",
    autoClose: 800,
    hideProgressBar: true,
    closeOnClick: true,
    pauseOnHover: true,
    draggable: true,
    progress: undefined,
    theme: "light",
};

export default function List(props) {
    const [contacts, setContacts] = useState([]);
    const [loading, setLoading] = useState(false);
    const [totalRows, setTotalRows] = useState(0);
    const [itemsPerPage, setItemsPerPage] = useState(10);
    const axios = window.axios;

    const columns = [
        {
            name: 'First Name',
            selector: row => row.FirstName,
            cell: (row, index) => <EditableTextCell value={row.FirstName} fieldName="FirstName" placeholder="Enter first name" onCellChange={(e) => onCellChange(e, row)}/>
        },
        {
            name: 'Last Name',
            selector: row => row.LastName,
            cell: (row, index) => <EditableTextCell value={row.LastName} fieldName="LastName" placeholder="Enter last name" onCellChange={(e) => onCellChange(e, row)}/>
        },
        {
            name: 'Email',
            selector: row => row.Email,
            cell: (row, index) => <EditableTextCell value={row.Email} fieldName="Email" placeholder="Enter email" onCellChange={(e) => onCellChange(e, row)}/>
        },
        {
            name: 'Phone',
            selector: row => row.Phone,
            cell: (row, index) => <EditableTextCell value={row.Phone} fieldName="Phone" placeholder="Enter phone" onCellChange={(e) => onCellChange(e, row)}/>
        }
    ];

    const syncChangesToSalesforce = async (id, updatedData) => {
        setLoading(true);
        try {
            const response = await axios.patch(`/api/v1/contacts/${id}`, updatedData);

            if (response.data.success) {
                setLoading(false);
                toast.success('Contact updated successfully!', toastConfig);
            } else {
                fetchContacts(1);
                toast.error('Failed to update contact', toastConfig);
            }
        } catch (err) {
            fetchContacts(1);
            toast.error('Failed to update contact', toastConfig);
            console.error(`Failed to update contact id - ${id}`, err);
        }
    };

    const handleSalesforceDataSynchronization = useCallback(debounce(syncChangesToSalesforce, 1500), []);

    const onCellChange = (e, row) => {
        const { name, value } = e.target
        const selectedContact = cloneDeep(contacts).find(contact => contact.Id === row.Id);
        const updatedContacts = cloneDeep(contacts).map((contact) => (contact.Id === row.Id) ? { ...contact, [name]: value } : contact)
        setContacts(updatedContacts);
        handleSalesforceDataSynchronization(selectedContact.Id, { [name]: value });
    };

    const fetchContacts = async page => {
        setLoading(true);

        const response = await axios.get(`/api/v1/contacts?page=${page}&itemsPerPage=${itemsPerPage}`);

        setContacts(response.data.records);
        setTotalRows(response.data.totalContacts);
        setLoading(false);
    };

    const handlePageChange = page => {
        fetchContacts(page);
    };

    const handlePerRowsChange = async (newPerPage, page) => {
        setLoading(true);

        const response = await axios.get(`/api/v1/contacts?page=${page}&itemsPerPage=${newPerPage}`);

        setContacts(response.data.records);
        setItemsPerPage(newPerPage);
        setLoading(false);
    };

    useEffect(() => {
        fetchContacts(DEFAULT_PAGE);
    }, []);

    return (
        <div className="container contacts my-5">
            <h1 className="mb-3">Contacts</h1>

            <DataTable
                columns={columns}
                data={contacts}
                progressPending={loading}
                pagination
                paginationServer
                paginationTotalRows={totalRows}
                onChangeRowsPerPage={handlePerRowsChange}
                onChangePage={handlePageChange}
            />
            <ToastContainer />
        </div>
    )
}
