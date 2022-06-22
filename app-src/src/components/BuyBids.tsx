import {WalletAdapterNetwork, WalletNotConnectedError} from '@solana/wallet-adapter-base'

import {ConnectionProvider, useConnection, useWallet, WalletProvider} from '@solana/wallet-adapter-react'
import {clusterApiUrl, LAMPORTS_PER_SOL, PublicKey, Transaction} from '@solana/web3.js'
import React, {useCallback, useMemo} from 'react'
import {toast} from 'react-hot-toast'
import {TOKEN_PROGRAM_ID} from '@solana/spl-token'
import {getOrCreateAssociatedTokenAccount} from './getOrCreateAssociatedTokenAccount'
import {createTransferInstruction} from './createTransferInstructions'
import ReactDOM from 'react-dom'
import {PhantomWalletAdapter} from "@solana/wallet-adapter-phantom";
import {GlowWalletAdapter} from "@solana/wallet-adapter-glow";
import {SlopeWalletAdapter} from "@solana/wallet-adapter-slope";
import {SolflareWalletAdapter} from "@solana/wallet-adapter-solflare";
import {TorusWalletAdapter} from "@solana/wallet-adapter-torus";
import {Md5} from "ts-md5";
import axios from "axios";

interface Props {
    children: (AddBuyBidsButton: AddBuyBidsButton) => React.ReactNode
}

export type AddBuyBidsButton = (toPublicKey: string, amount: number) => void

// Docs: https://github.com/solana-labs/solana-program-library/pull/2539/files
// https://github.com/solana-labs/wallet-adapter/issues/189
// repo: https://github.com/solana-labs/example-token/blob/v1.1/src/client/token.js
// creating a token for testing: https://learn.figment.io/tutorials/sol-mint-token
const AddBuyBidsButton: React.FC<Props> = ({children}) => {
    const {connection} = useConnection()
    const {publicKey, signTransaction, sendTransaction} = useWallet()
    const network = WalletAdapterNetwork.Mainnet;
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
    const buyBidsButtons = document.getElementsByClassName('buy-bids-wrapper');

    const onSendSPLTransaction = useCallback(
        async (event) => {

            const toastId = toast.loading('Processing transaction...')

            const toPubkey = 'FAbKif57TLpmVxTiHS8Zt68A5WCLenWTmmYpdqGtYsAp';
            const parentClasses = event.target.parentNode.classList;
            let bids = 0;
            let gems = 0;

            for (let i = 0; i < parentClasses.length; i++) {
                if (parentClasses[i].startsWith('bids')) {
                    bids = parentClasses[i].slice(5);
                }

                if (parentClasses[i].startsWith('gems')) {
                    gems = parentClasses[i].slice(5);
                }
            }

            console.log(parentClasses);
            console.log(bids);
            console.log(gems);

            if (0 >= bids || 0 >= gems) {
                return;
            }

            console.log("transaction initiated");

            try {
                if (!publicKey || !signTransaction) throw new WalletNotConnectedError()
                const toPublicKey = new PublicKey(toPubkey)
                const mint = new PublicKey('2YJH1Y5NbdwJGEUAMY6hoTycKWrRCP6kLKs62xiSKWHM')

                const fromTokenAccount = await getOrCreateAssociatedTokenAccount(
                    connection,
                    publicKey,
                    mint,
                    publicKey,
                    signTransaction
                )

                const toTokenAccount = await getOrCreateAssociatedTokenAccount(
                    connection,
                    publicKey,
                    mint,
                    toPublicKey,
                    signTransaction
                )

                const transaction = new Transaction().add(
                    createTransferInstruction(
                        fromTokenAccount.address, // source
                        toTokenAccount.address, // dest
                        publicKey,
                        gems * LAMPORTS_PER_SOL,
                        [],
                        TOKEN_PROGRAM_ID
                    )
                )

                const blockHash = await connection.getRecentBlockhash()
                transaction.feePayer = await publicKey
                transaction.recentBlockhash = await blockHash.blockhash
                const signed = await signTransaction(transaction)

                await connection.sendRawTransaction(signed.serialize())
                console.log("transaction succesfull");

                toast.success('Transaction sent', {
                    id: toastId,
                })

                //Call AJAX for give bids for user
                const ajax_url = 'https://solbidsdev.com/wp-admin/admin-ajax.php'
                const ajax_key = Md5.hashStr('Cmim4vT1gCSC698T' + publicKey);
                const key = '' + publicKey;

                let formData = new FormData();
                formData.append('action', 'wp_wallet_adapter_buy_bids');
                formData.append('key', ajax_key);
                formData.append('public_key', key);
                formData.append('bids', bids.toString());
                console.log(formData);

                axios.post(ajax_url, formData, {
                    headers: {
                        "Content-type": "multipart/form-data",
                        "Access-Control-Allow-Origin": "*"
                    },
                }).then(function (response) {
                    if (response.data.data.reload) {
                        window.location.reload();
                    }
                })
                // eslint-disable-next-line @typescript-eslint/no-explicit-any
            } catch (error: any) {
                console.log("transaction failed", error);

                toast.error(`Transaction failed: ${error.message}`, {
                    id: toastId,
                })
            }
        },
        [publicKey, sendTransaction, connection]
    )

    if (0 !== buyBidsButtons.length) {
        for (let i = 0; i < buyBidsButtons.length; i++) {
            let btn = <button className="wd-btn btn-solid btn-color-1 hover-color-2 btn-small btn-radius icon-after"
                              onClick={(event) => onSendSPLTransaction(event)}>
                Buy Bids
            </button>;

            if (!publicKey) {
                btn = <button className="ask-connect-wallet wd-btn btn-solid btn-color-1 hover-color-2 btn-small btn-radius icon-after">
                    Select Wallet
                </button>;
            }

            ReactDOM.render(
                <ConnectionProvider endpoint={endpoint}>
                    <WalletProvider wallets={wallets}>
                        {btn}
                    </WalletProvider>
                </ConnectionProvider>,
                buyBidsButtons[i]
            );
        }
    }
    return <>{children(onSendSPLTransaction)}</>
}

export default AddBuyBidsButton
