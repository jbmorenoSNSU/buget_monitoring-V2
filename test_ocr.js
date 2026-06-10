import Tesseract from 'tesseract.js';

async function test() {
    console.log("Creating worker...");
    const worker = await Tesseract.createWorker('eng', 1, {
        logger: m => console.log(m)
    });
    console.log("Worker created successfully");
    await worker.terminate();
}

test().catch(console.error);
