import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';
import './index.css';
import reportWebVitals from './reportWebVitals';
import EmptyWrappers from "./components/EmptyWrappers";

const appAnchorElement = document.getElementById('wp-wallet-adapter-wrapper')
//const appAnchorElement = document.getElementById('root')
ReactDOM.render(
    <React.StrictMode>
        <App/>
    </React.StrictMode>,
    appAnchorElement
);

// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
reportWebVitals();
