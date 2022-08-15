import uploadFile, {deleteFile} from "@/api/upload";

export default class FileService {
    constructor() {
    }

    async upload(file, path = 'uploads', fileName = 'file') {
        return await uploadFile(file, fileName, path);
    }

    async remove(fileName) {
        await deleteFile(fileName);
    }

    download (path) {
        const link = document.createElement('a');
        link.href = path;
        link.click();
    }
}
