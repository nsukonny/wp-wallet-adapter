import {WalletAdapterNetwork, WalletNotConnectedError} from '@solana/wallet-adapter-base';
import {ConnectionProvider, useConnection, useWallet, WalletProvider} from '@solana/wallet-adapter-react';
import {clusterApiUrl, Keypair, SystemProgram, Transaction} from '@solana/web3.js';
import React, {FC, useCallback, useMemo} from 'react';
import ReactDOM from "react-dom";
import {PhantomWalletAdapter} from "@solana/wallet-adapter-phantom";
import {GlowWalletAdapter} from "@solana/wallet-adapter-glow";
import {SlopeWalletAdapter} from "@solana/wallet-adapter-slope";
import {SolflareWalletAdapter} from "@solana/wallet-adapter-solflare";
import {TorusWalletAdapter} from "@solana/wallet-adapter-torus";

/**
 * Add Buy Bids buttons on needed page
 *
 * @constructor
 */
export const AddBuyBidsButton: FC = () => {
    const buyBidsButtons = document.getElementsByClassName('buy-bids-wrapper');
    console.log('buyBidsButtons - ' + buyBidsButtons.length);
    const network = WalletAdapterNetwork.Devnet;
    const endpoint = useMemo(() => clusterApiUrl(network), [network]);

    const wallets = useMemo(
        () => [
            new PhantomWalletAdapter(),
            new GlowWalletAdapter(),
            new SlopeWalletAdapter(),
            new SolflareWalletAdapter({network}),
            new TorusWalletAdapter(),
        ],
        [network]
    );

    if (0 !== buyBidsButtons.length) {
        for (let i = 0; i < buyBidsButtons.length; i++) {
            let classes = buyBidsButtons[i].classList,
                bids = 0,
                gems = 0;

            for (let classNumber = 0; classNumber < classes.length; classNumber++) {
                if (classes[i].startsWith('gems')) {
                    gems = parseInt(classes[i].slice(5));
                }

                if (classes[i].startsWith('bids')) {
                    bids = parseInt(classes[i].slice(5));
                }
            }
            console.log('Called render button');
            ReactDOM.render(
                <>
                    <ConnectionProvider endpoint={endpoint}>
                        <WalletProvider wallets={wallets} autoConnect>
                            <BuyBidsButton/>
                        </WalletProvider>
                    </ConnectionProvider>
                </>,
                buyBidsButtons[i]
            );
        }
    }

    return null;
};

/**
 * Buy Bids button
 *
 * @constructor
 */
export const BuyBidsButton: FC = (props) => {
    const {connection} = useConnection();
    const {publicKey, sendTransaction} = useWallet();

    const buyBids = useCallback(async () => {
        if (!publicKey) throw new WalletNotConnectedError();

        const transaction = new Transaction().add(
            SystemProgram.transfer({
                fromPubkey: publicKey,
                toPubkey: Keypair.generate().publicKey,
                lamports: 1,
            })
        );

        const signature = await sendTransaction(transaction, connection);

        await connection.confirmTransaction(signature, 'processed');
    }, [publicKey, sendTransaction, connection]);

    if (!publicKey) {
        return (
            <button className="wd-btn btn-solid btn-color-1 hover-color-2 btn-small btn-radius  icon-after"
            >
                Connect Wallet
            </button>);
    }

    return (
        <button
            className="wd-btn btn-solid btn-color-1 hover-color-2 btn-small btn-radius  icon-after"
            onClick={buyBids}
        >Buy Now</button>
    );
};

export default AddBuyBidsButton;
