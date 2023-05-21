import React, { useState } from 'react';
import { Formik } from 'formik';
import isEmpty from 'lodash/isEmpty';
import { ToastContainer, toast } from 'react-toastify';
import aes from 'crypto-js/aes';
import encHex from 'crypto-js/enc-hex';
import padZeroPadding from 'crypto-js/pad-zeropadding';
import { toastConfig } from "../../../Common/Toaster/Toast.config";

const USER_INFO_STORAGE_KEY = 'sf_user';

export default function Connection(props) {
    const axios = window.axios;
    const [isLoggingOut, setIsLoggingOut] = useState(false);
    const {
        userInfo,
        updateUserInfo,
        loginCryptoConfig: {
            key: cryptoKey,
            iv: cryptoIv
        }
    } = props;

    const encryptData = (data) => {
        const key = encHex.parse(cryptoKey);
        const iv =  encHex.parse(cryptoIv);

        return aes.encrypt(data, key, { iv:iv, padding: padZeroPadding }).toString();
    };

    const connectSalesforceAccount = async ({ username, password, token }, { setSubmitting }) => {
        try {
            const response = await axios.post('/api/v1/salesforce/connect', {
                Username: encryptData(username),
                Password: encryptData(password),
                Token: encryptData(token)
            });
            setSubmitting(false);
            const { success, message, userInfo } = response.data;

            if (success) {
                updateUserInfo(userInfo);
                localStorage.setItem(USER_INFO_STORAGE_KEY, JSON.stringify(userInfo));
                toast.success(message, toastConfig);
            } else {
                toast.error(message, toastConfig);
            }
        } catch (err) {
            setSubmitting(false);
            console.error('Failed to connect to Salesforce account. Reason: ', err);
            toast.error('Failed to connect to Salesforce account', toastConfig);
        }
    };

    const logout = async () => {
        setIsLoggingOut(true);
        try {
            const response = await axios.post('/api/v1/salesforce/logout');

            setIsLoggingOut(false);
            if (response.data.success) {
                localStorage.removeItem(USER_INFO_STORAGE_KEY);
                updateUserInfo({});
            } else {
                toast.error(response.data.message, toastConfig);
            }
        } catch (err) {
            setIsLoggingOut(false);
            console.error('Failed to log out Salesforce account', err)
            toast.error('Failed to log out Salesforce account', toastConfig);
        }
    };

    return (
        <div className="container">
            <h1 className="my-3 fw-bold">Account Details</h1>
            {isEmpty(userInfo) && <Formik
                initialValues={{username: '', password: '', token: ''}}
                validate={values => {
                    const errors = {};
                    Object.entries(values).forEach(([key, value]) => {
                        if (!value) {
                            errors[key] = 'Required';
                        } else {
                            if ('username' === key && !/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i.test(values[key])) {
                                errors.username = 'Invalid email address';
                            }
                        }
                    });

                    return errors;
                }}
                onSubmit={connectSalesforceAccount}
            >
                {({
                      values,
                      errors,
                      touched,
                      handleChange,
                      handleBlur,
                      handleSubmit,
                      isSubmitting,
                      /* and other goodies */
                  }) => (
                    <form onSubmit={handleSubmit}>
                        <div className="row">
                            <div className="col-sm-4 mb-3">
                                <label htmlFor="username" className="form-label fw-bold">Username:</label>
                                <input
                                    id="username"
                                    type="email"
                                    name="username"
                                    className="form-control"
                                    onChange={handleChange}
                                    onBlur={handleBlur}
                                    value={values.username}
                                />
                                <small className="text-danger">
                                    {errors.username && touched.username && errors.username}
                                </small>
                            </div>

                            <div className="col-sm-4 mb-3">
                                <label htmlFor="password" className="form-label fw-bold">Password:</label>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    className="form-control"
                                    onChange={handleChange}
                                    onBlur={handleBlur}
                                    value={values.password}
                                />
                                <small className="text-danger">
                                    {errors.password && touched.password && errors.password}
                                </small>
                            </div>

                            <div className="col-sm-4 mb-3">
                                <label htmlFor="token" className="form-label fw-bold">Token:</label>
                                <input
                                    id="token"
                                    type="text"
                                    name="token"
                                    className="form-control"
                                    onChange={handleChange}
                                    onBlur={handleBlur}
                                    value={values.token}
                                />
                                <small className="text-danger">
                                    {errors.token && touched.token && errors.token}
                                </small>
                            </div>
                        </div>

                        <div className="row justify-content-end">
                            <div className="col-sm-3">
                                <button className="btn btn-primary w-50 float-end" type="submit"
                                        disabled={isSubmitting}>
                                    {isSubmitting &&
                                        <span className="spinner-border spinner-border-sm me-1" role="status"
                                              aria-hidden="true"></span>}
                                    {isSubmitting ? 'Connecting...' : 'Connect'}
                                </button>
                            </div>
                        </div>

                    </form>
                )}
            </Formik>}
            {!isEmpty(userInfo) && <div className="row">
                <p>You are already logged in as {userInfo.username}</p>
                <div className="col-sm-3">
                    <button className="btn btn-primary w-50" type="button" onClick={logout} disabled={isLoggingOut}>
                        {isLoggingOut &&
                            <span className="spinner-border spinner-border-sm me-1" role="status"
                                  aria-hidden="true"></span>}
                        {isLoggingOut ? 'Logging out...' : 'Log out'}
                    </button>
                </div>
            </div>
            }
            <ToastContainer />
        </div>
    )
}
