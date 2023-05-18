import React, { useState } from 'react';
import { Tab, Tabs, TabList, TabPanel } from 'react-tabs';
import Connection from "./Connections";
import Lead from "./Leads";
import Campaign from "./Campaigns";

const Home = (props) => {
    const tabs  = [
        {
            id: 1,
            title: 'Connections',
            component: <Connection {...props}/>
        },
        {
            id: 2,
            title: 'Leads',
            component: <Lead {...props}/>
        },
        {

            id: 3,
            title: 'Campaigns',
            component: <Campaign {...props}/>
        }
    ];

    return (
        <div className="container">
            <h1 className="fw-bold my-3">Welcome to Home page</h1>

            <Tabs>
                <TabList>
                    {tabs.map(({id, title}) => <Tab key={id}>{title}</Tab>)}
                </TabList>

                {tabs.map(({id, component}) =>
                    <TabPanel key={id}>
                        {component}
                    </TabPanel>
                )}

            </Tabs>
        </div>
    )
}

export default Home;
