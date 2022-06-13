import { Button } from '@mui/material';
import { useConnection, useWallet } from '@solana/wallet-adapter-react';
import { Keypair, SystemProgram, Transaction, TransactionSignature } from '@solana/web3.js';
import { FC, useCallback } from 'react';

export const SendTransaction: FC = () => {
    const { connection } = useConnection();
    const { publicKey, sendTransaction } = useWallet();
    const notify = 'empty';

    const onClick = useCallback(async () => {
        if (!publicKey) {
            console.log('Wallet not connected!');
            return;
        }

        let signature: TransactionSignature = '';
        try {
            const transaction = new Transaction().add(
                SystemProgram.transfer({
                    fromPubkey: publicKey,
                    toPubkey: Keypair.generate().publicKey,
                    lamports: 1,
                })
            );

            signature = await sendTransaction(transaction, connection);
            console.log('Transaction sent:' + signature);

            await connection.confirmTransaction(signature, 'processed');
            console.log('Transaction successful!' + signature);
        } catch (error: any) {
            console.log(`Transaction failed! ${error?.message}` + signature);
            return;
        }
    }, [publicKey, notify, connection, sendTransaction]);

    return (
        <Button variant="contained" color="secondary" onClick={onClick} >
            Send Transaction (devnet)
        </Button>
    );
};
