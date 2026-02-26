import { exec } from 'child_process';
import { promisify } from 'util';

const execAsync = promisify(exec);
const PORT = 3000;

async function stopSocketServer() {
    try {
        const isWindows = process.platform === 'win32';

        if (isWindows) {
            // Windows: Find and kill process on port 3000
            try {
                const { stdout } = await execAsync(`netstat -ano | findstr :${PORT}`);
                const lines = stdout.trim().split('\n');
                const pids = new Set();

                lines.forEach(line => {
                    const match = line.match(/\s+(\d+)\s*$/);
                    if (match && match[1] !== '0') {
                        pids.add(match[1]);
                    }
                });

                if (pids.size === 0) {
                    console.log(`No process found on port ${PORT}`);
                    return;
                }

                for (const pid of pids) {
                    try {
                        await execAsync(`taskkill /F /PID ${pid}`);
                        console.log(`✓ Killed process ${pid}`);
                    } catch (err) {
                        // Process might already be dead
                    }
                }

                console.log('Socket server stopped');
            } catch (err) {
                console.log(`No process found on port ${PORT}`);
            }
        } else {
            // Linux/Mac: Find and kill process on port 3000
            try {
                const { stdout } = await execAsync(`lsof -ti:${PORT}`);
                const pid = stdout.trim();

                if (!pid) {
                    console.log(`No process found on port ${PORT}`);
                    return;
                }

                await execAsync(`kill -9 ${pid}`);
                console.log(`✓ Killed process ${pid}`);
                console.log('Socket server stopped');
            } catch (err) {
                console.log(`No process found on port ${PORT}`);
            }
        }
    } catch (error) {
        console.error('Error stopping socket server:', error.message);
        process.exit(1);
    }
}

stopSocketServer();
