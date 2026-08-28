/**
 * Script để chuyển đổi tất cả các Vue page sang persistent layout:
 * 1. Xóa <AuthenticatedLayout> wrapper trong template
 * 2. Xóa import AuthenticatedLayout
 * 3. Giữ nguyên nội dung bên trong <AuthenticatedLayout>
 */

const fs = require('fs')
const path = require('path')

const pagesDir = path.join(__dirname, '../resources/js/Pages')

function processVueFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf-8')
    const original = content

    // Skip auth pages and Welcome
    const relativePath = path.relative(pagesDir, filePath).replace(/\\/g, '/')
    const skipFiles = ['Auth/', 'Welcome.vue', 'Profile/']
    if (skipFiles.some(s => relativePath.startsWith(s))) {
        return
    }

    // Check if file uses AuthenticatedLayout in template
    if (!content.includes('<AuthenticatedLayout>') && !content.includes('<AuthenticatedLayout ')) {
        return
    }

    console.log(`Processing: ${relativePath}`)

    // Remove import AuthenticatedLayout line
    content = content.replace(/^import AuthenticatedLayout from '@\/Layouts\/AuthenticatedLayout\.vue'.*\n/m, '')
    content = content.replace(/^import AuthenticatedLayout from "@\/Layouts\/AuthenticatedLayout\.vue".*\n/m, '')

    // Remove <AuthenticatedLayout> opening tag (with or without props)
    content = content.replace(/\n?[ \t]*<AuthenticatedLayout[^>]*>\n/g, '\n')

    // Remove </AuthenticatedLayout> closing tag
    content = content.replace(/\n?[ \t]*<\/AuthenticatedLayout>\n?/g, '\n')

    if (content !== original) {
        fs.writeFileSync(filePath, content, 'utf-8')
        console.log(`  ✅ Updated: ${relativePath}`)
    }
}

function walkDir(dir) {
    const entries = fs.readdirSync(dir, { withFileTypes: true })
    for (const entry of entries) {
        const fullPath = path.join(dir, entry.name)
        if (entry.isDirectory()) {
            walkDir(fullPath)
        } else if (entry.name.endsWith('.vue')) {
            processVueFile(fullPath)
        }
    }
}

console.log('Starting persistent layout migration...')
walkDir(pagesDir)
console.log('Done!')
