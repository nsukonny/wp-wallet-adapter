import {WalletAdapterNetwork} from '@solana/wallet-adapter-base';
import {ConnectionProvider, useWallet, WalletProvider} from '@solana/wallet-adapter-react';
import {WalletModalProvider, WalletMultiButton} from '@solana/wallet-adapter-react-ui';
import {
    GlowWalletAdapter,
    PhantomWalletAdapter,
    SlopeWalletAdapter,
    SolflareWalletAdapter,
    TorusWalletAdapter,
} from '@solana/wallet-adapter-wallets';
import {clusterApiUrl} from '@solana/web3.js';
import React, {FC, ReactNode, useMemo, useState} from 'react';
import AddBuyBidsButtonComp, {AddBuyBidsButton} from './components/BuyBids'
import LogInUser from "./components/Authorization";

require('./App.css');
require('@solana/wallet-adapter-react-ui/styles.css');

interface Props {
    onSendSPLTransaction: AddBuyBidsButton
}

const BuyButton: FC<Props> = ({onSendSPLTransaction}) => {
    const {publicKey} = useWallet()
    const [amount, setAmount] = useState(0);

    return null;

    return (
        <form onSubmit={(e) => {
            e.preventDefault();
        }}>
            <input type="number" value={amount} onChange={(e) => setAmount(parseInt(e.target.value))}/>
            <button
                type='submit'
                onClick={() => publicKey && onSendSPLTransaction(publicKey?.toString(), 4)}>
                Send SGEMS
            </button>
        </form>
    )
}

const Context: FC<{ children: ReactNode }> = ({children}) => {
    // The network can be set to 'devnet', 'testnet', or 'mainnet-beta'.
    const network = WalletAdapterNetwork.Mainnet;

    // You can also provide a custom RPC endpoint.
    const endpoint = useMemo(() => clusterApiUrl(network), [network]);

    // @solana/wallet-adapter-wallets includes all the adapters but supports tree shaking and lazy loading --
    // Only the wallets you configure here will be compiled into your application, and only the dependencies
    // of wallets that your users connect to will be loaded.
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

    return (
        <ConnectionProvider endpoint={endpoint}>
            <WalletProvider wallets={wallets} autoConnect>
                <WalletModalProvider>{children}</WalletModalProvider>
            </WalletProvider>
        </ConnectionProvider>
    );
};

const Content: FC = () => {

    const {publicKey} = useWallet();

    if (!publicKey) {
        return (
            <div className="App">
                <WalletMultiButton children="Connect Wallet"/>
            </div>
        );
    }

    return (
        <div className="App">
            <WalletMultiButton/>
        </div>
    );
};
const App: FC = () => {

    return (
        <Context>
            <Content/>
            <AddBuyBidsButtonComp>
                {(onSendSPLTransaction) => <BuyButton onSendSPLTransaction={onSendSPLTransaction}/>}
            </AddBuyBidsButtonComp>
            <LogInUser/>
        </Context>
    );
};
export default App;
