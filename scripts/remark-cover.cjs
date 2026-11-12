/**
 * Resolve the package-relative cover through the site's generated static directory.
 * The same Markdown also resolves the original asset from the package checkout.
 * @param {object} line Documentation line with a cover asset.
 * @returns {function} Remark transformer.
 */
function remarkCover(line) {
    return (tree) => {
        for (const block of tree.children) {
            if (block.type !== 'paragraph') continue;
            for (const node of block.children) {
                if (node.type === 'image' && node.url === `../${line.cover}`) {
                    node.url = `/img/${line.id}-cover.png`;
                }
            }
        }
    };
}

module.exports = remarkCover;
