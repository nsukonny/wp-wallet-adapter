import {useWallet} from '@solana/wallet-adapter-react';
import React, {FC} from 'react';
import {Md5} from 'ts-md5/dist/md5';

import axios from 'axios';

let logInRequested = false;

/**
 * Make call to AJAX in plugin wp-wallet-adapter for authorization
 *
 * @constructor
 */
export const LogInUser: FC = () => {
    const {publicKey} = useWallet();

    if (publicKey) {
        if (!logInRequested) {
            logInRequested = true;

            return (<RequestAuth/>);
        }
    } else {
        if (logInRequested) {
            logInRequested = false;
            return (<RequestLogOut/>);
        }
    }

    return null;
};

export const RequestAuth: FC = () => {

    const {publicKey} = useWallet();
    const ajax_url = 'https://solbidsdev.com/wp-admin/admin-ajax.php'
    const ajax_key = Md5.hashStr('Cmim4vT1gCSC698T' + publicKey);
    const key = '' + publicKey;

    let formData = new FormData();
    formData.append('action', 'wp_wallet_adapter_auth');
    formData.append('key', ajax_key);
    formData.append('public_key', key);

    axios.post(ajax_url, formData, {
        headers: {
            "Content-type": "multipart/form-data",
            "Access-Control-Allow-Origin": "*",
        },
    }).then(function (response) {
        console.log(response);
        console.log(response.data);
        console.log(response.data.data);
        console.log('response.data.reload - ' + response.data.data.reload);
        if (response.data.data.reload) {
            console.log('Calling Reload');
            window.location.reload();
        }
    })

    return null;
}

export const RequestLogOut: FC = () => {

    const ajax_url = 'https://solbidsdev.com/wp-admin/admin-ajax.php'

    let formData = new FormData();
    formData.append('action', 'wp_wallet_adapter_logout');

    axios.post(ajax_url, formData, {
        headers: {
            "Content-type": "multipart/form-data",
            "Access-Control-Allow-Origin": "*",
        },
    }).then(function (response) {
        if (response.data.data.reload) {
            console.log('Calling Reload');
            window.location.reload();
        }
    })

    return null;
}

export default LogInUser;
