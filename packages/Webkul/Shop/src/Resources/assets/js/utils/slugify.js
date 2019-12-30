import slugify from 'slugify'

export default (value) => {
    return slugify(value, {
        lower: true,
        replacement: '-',
    })
}