import React, { useState, useEffect } from 'react';
import Modal from 'react-modal';
import {
    Chart as ChartJS,
    ArcElement,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js';
import { Bar, Doughnut } from 'react-chartjs-2';
import isEmpty from 'lodash/isEmpty';

Modal.setAppElement('#app');
ChartJS.register(
    ArcElement,
    Tooltip,
    Legend,
    CategoryScale,
    LinearScale,
    BarElement,
    Title
);

const customStyles = {
    content: {
        top: '50%',
        left: '50%',
        right: 'auto',
        bottom: 'auto',
        marginRight: '-50%',
        transform: 'translate(-50%, -50%)',
    },
};

export default function Chart(props) {
    const { isOpen, setIsOpen, campaignId, onChartModalClose } = props;
    const [loading, setLoading] = useState(false);
    const [emptyChartData, setEmptyChartData] = useState(false);
    const [typesOfMemberDataLabels, setTypesOfMemberDataLabels] = useState([]);
    const [typesOfMemberDataset, setTypesOfMemberDataset] = useState([]);
    const [typesOfMemberChartEmpty, setTypesOfMemberChartEmpty] = useState(false);
    const [membersTitleChartEmpty, setMembersTitleChartEmpty] = useState(false);
    const [membersTitleVps, setMembersTitleVps] = useState([]);
    const [membersTitleCLevels, setMembersTitleCLevels] = useState([]);
    const [membersTitleManagers, setMembersTitleManagers] = useState([]);

    const typesOfMemberData = {
        labels: typesOfMemberDataLabels,
        datasets: [
            {
                label: 'Number of Members',
                data: typesOfMemberDataset,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                ]
            },
        ],
    };

    const membersTitleOptions = {
        responsive: true
    };

    const membersTitleLabels = ['VPs', 'C-Levels', 'Managers'];

    const membersTitleData = {
        labels: membersTitleLabels,
        datasets: [
            {
                label: 'VPs',
                data: membersTitleVps,
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
            },
            {
                label: 'C-Levels',
                data: membersTitleCLevels,
                backgroundColor: 'rgba(53, 162, 235, 0.5)',
            },
            {
                label: 'Managers',
                data: membersTitleManagers,
                backgroundColor: 'rgba(241, 124, 235, 0.8)',
            }
        ],
    };

    const fetchChartData = async () => {
        setLoading(true);

        const response = await axios.get(`/api/v1/campaigns/${campaignId}/chart`);

        setLoading(false);

        if (isEmpty(response.data)) {
            setEmptyChartData(true);
            return;
        }

        const { typesOfMemberChart, membersTitleChart } = response.data;

        if (!isEmpty(typesOfMemberChart)) {
            setTypesOfMemberDataLabels(typesOfMemberChart.labels);
            setTypesOfMemberDataset(typesOfMemberChart.dataSet);
        } else {
            setTypesOfMemberChartEmpty(true);
        }

        if (!isEmpty(membersTitleChart)) {
            const { dataSet: { VPs, CLevels, Managers } } = membersTitleChart;

            setMembersTitleVps(VPs);
            setMembersTitleCLevels(CLevels);
            setMembersTitleManagers(Managers);
        } else {
            setMembersTitleChartEmpty(true);
        }

    };

    const renderCharts = () => {
        if (emptyChartData) {
            return <p className="text-center">Charts data is not available at this moment.</p>;
        }

        return <>
            <div className="row">
                <div className="col-12">
                    <h5 className="fw-bold my-3 text-center">Types of Member</h5>

                    {typesOfMemberChartEmpty ?
                        <p className="text-center">The Chart data is not available at this moment.</p> :
                        <Doughnut data={typesOfMemberData} />}
                </div>
            </div>

            <div className="row">
                <div className="col-12">
                    <h5 className="fw-bold my-3 text-center">Members Title</h5>

                    {membersTitleChartEmpty ?
                        <p className="text-center">The Chart data is not available at this moment.</p> :
                        <Bar options={membersTitleOptions} data={membersTitleData} />
                    }
                </div>
            </div>
        </>;
    };

    useEffect(() => {
        if (campaignId) {
            fetchChartData();
        }
    }, [campaignId]);

    const closeModal = () => {
        setIsOpen(false);
        onChartModalClose();
    };

    return (
        <Modal
            isOpen={isOpen}
            onRequestClose={closeModal}
            style={customStyles}
        >
            <div className="modal-header">
                <h3 className="modal-title fw-bold">Campaign Members</h3>
                <button type="button" onClick={closeModal} className="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div className="modal-body">
                {loading ?
                    <div className="row">
                        <div className="col-md-12 p-5 text-center">
                            <span className="spinner-border spinner-border-sm me-1" role="status"
                                  aria-hidden="true"></span>Loading..
                        </div>
                    </div> : renderCharts()
                }
            </div>
        </Modal>
    )
}
