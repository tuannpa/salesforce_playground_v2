import React, { useState } from 'react';
import isEmpty from 'lodash/isEmpty';

export default function Campaign(props) {
    const axios = window.axios;
    const { userInfo } = props;

    return (
        <div className="container">
            <div className="row">
                <h1>Campaign Details</h1>

            </div>

            {isEmpty(userInfo) && <div className="row">
                <p>Please connect to your SFDC Account in the tab Connections, then you will be able to see your Campaigns data.</p>
            </div>
            }
        </div>
    )
}
