SELECT COUNT(*) AS EntriesCount
FROM article_comments
WHERE CategoryKey = '[CategoryKey]'
GROUP BY ArticleCommentID;